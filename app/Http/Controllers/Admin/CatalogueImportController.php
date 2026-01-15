<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalogue;
use App\Models\CatalogueField;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CatalogueImportController extends Controller
{
    /**
     * Download sample Excel template
     */
    public function downloadSample()
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id;

        if (!$adminId) {
            return back()->with('error', 'Tenant not found.');
        }

        // Get fields for this tenant
        $fields = CatalogueField::forTenant($adminId)->ordered()->get();

        if ($fields->isEmpty()) {
            return back()->with('error', 'Please create at least one field before downloading the sample.');
        }

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Catalogue Data');

        // Header style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        // Add headers
        $col = 'A';
        foreach ($fields as $field) {
            $headerText = $field->field_name;
            if ($field->is_required) {
                $headerText .= ' *';
            }
            if ($field->is_unique) {
                $headerText .= ' (Unique)';
            }

            $sheet->setCellValue($col . '1', $headerText);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Apply header style
        $lastCol = chr(ord('A') + count($fields) - 1);
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);

        // Add sample data row
        $col = 'A';
        foreach ($fields as $field) {
            $sampleValue = $this->getSampleValue($field);
            $sheet->setCellValue($col . '2', $sampleValue);
            $col++;
        }

        // Add instructions sheet
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('Instructions');

        $instructionSheet->setCellValue('A1', 'Import Instructions');
        $instructionSheet->setCellValue('A3', '1. Fill in the "Catalogue Data" sheet with your product data.');
        $instructionSheet->setCellValue('A4', '2. Fields marked with * are required.');
        $instructionSheet->setCellValue('A5', '3. Fields marked with (Unique) must have unique values - duplicates will cause errors.');
        $instructionSheet->setCellValue('A6', '4. Do not modify the header row.');
        $instructionSheet->setCellValue('A7', '5. You can add as many rows as needed.');
        $instructionSheet->setCellValue('A9', 'Field Types:');

        $row = 10;
        foreach ($fields as $field) {
            $typeInfo = "• {$field->field_name}: {$field->field_type}";
            if ($field->field_type === 'select' && $field->options) {
                $typeInfo .= " (Options: " . implode(', ', $field->options) . ")";
            }
            $instructionSheet->setCellValue("A{$row}", $typeInfo);
            $row++;
        }

        $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $instructionSheet->getColumnDimension('A')->setWidth(80);

        // Set first sheet as active
        $spreadsheet->setActiveSheetIndex(0);

        // Generate file
        $filename = 'catalogue_template_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Import Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id;

        if (!$adminId) {
            return back()->with('error', 'Tenant not found.');
        }

        // Get fields for this tenant
        $fields = CatalogueField::forTenant($adminId)->ordered()->get();

        if ($fields->isEmpty()) {
            return back()->with('error', 'Please create fields before importing data.');
        }

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            $successCount = 0;
            $errorRows = [];
            $uniqueValues = []; // Track unique values for validation

            // Pre-load existing unique values from database
            foreach ($fields as $field) {
                if ($field->is_unique) {
                    $existingData = Catalogue::where('admin_id', $adminId)->get();
                    $uniqueValues[$field->field_key] = [];
                    foreach ($existingData as $item) {
                        if (isset($item->data[$field->field_key]) && !empty($item->data[$field->field_key])) {
                            $uniqueValues[$field->field_key][] = $item->data[$field->field_key];
                        }
                    }
                }
            }

            // Process each row (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = [];
                $rowErrors = [];
                $colIndex = 0;

                foreach ($fields as $field) {
                    $col = chr(65 + $colIndex); // A, B, C, etc.
                    $cellValue = $sheet->getCell($col . $row)->getValue();
                    $cellValue = trim($cellValue ?? '');
                    $rowData[$field->field_key] = $cellValue;

                    // Validate number type
                    if ($field->field_type === 'number' && !empty($cellValue) && !is_numeric($cellValue)) {
                        $rowErrors[] = "{$field->field_name} must be a number";
                    }

                    // Validate select options
                    if ($field->field_type === 'select' && !empty($cellValue) && $field->options) {
                        if (!in_array($cellValue, $field->options)) {
                            $rowErrors[] = "{$field->field_name} must be one of: " . implode(', ', $field->options);
                        }
                    }

                    // Validate unique
                    if ($field->is_unique && !empty($cellValue)) {
                        if (in_array($cellValue, $uniqueValues[$field->field_key] ?? [])) {
                            $rowErrors[] = "{$field->field_name} '{$cellValue}' already exists (duplicate)";
                        } else {
                            // Add to tracking for this import session
                            $uniqueValues[$field->field_key][] = $cellValue;
                        }
                    }

                    $colIndex++;
                }

                // Skip empty rows
                $hasData = collect($rowData)->filter(fn($v) => !empty($v) || $v === '0')->isNotEmpty();
                if (!$hasData) {
                    continue;
                }

                if (!empty($rowErrors)) {
                    $errorRows[] = [
                        'row' => $row,
                        'errors' => $rowErrors,
                        'data' => $rowData,
                    ];
                    continue;
                }

                // Create catalogue entry
                Catalogue::create([
                    'admin_id' => $adminId,
                    'data' => $rowData,
                    'is_active' => true,
                ]);

                $successCount++;
            }

            $message = "Import completed! {$successCount} products imported successfully.";

            if (!empty($errorRows)) {
                $errorCount = count($errorRows);
                $message .= " {$errorCount} rows had errors.";

                // Store errors in session for display
                session()->flash('import_errors', $errorRows);
            }

            if ($successCount === 0 && empty($errorRows)) {
                return back()->with('warning', 'No data found in the Excel file. Make sure data starts from row 2.');
            }

            return back()->with($successCount > 0 ? 'success' : 'warning', $message);

        } catch (\Exception $e) {
            \Log::error('Import error: ' . $e->getMessage());
            return back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    /**
     * Get sample value for field type
     */
    private function getSampleValue(CatalogueField $field): string
    {
        if ($field->field_type === 'select' && $field->options) {
            return $field->options[0] ?? 'Option 1';
        }

        if ($field->field_type === 'number') {
            return '100';
        }

        // Text type - generate contextual sample
        $name = strtolower($field->field_name);

        if (str_contains($name, 'product') || str_contains($name, 'name')) {
            return 'Wardrobe Handle';
        }
        if (str_contains($name, 'model')) {
            return '9005';
        }
        if (str_contains($name, 'size')) {
            return '224mm';
        }
        if (str_contains($name, 'finish')) {
            return 'Gold';
        }
        if (str_contains($name, 'material')) {
            return 'Aluminium';
        }
        if (str_contains($name, 'price')) {
            return '500';
        }
        if (str_contains($name, 'pack')) {
            return '25 pcs/box';
        }

        return 'Sample Value';
    }
}
