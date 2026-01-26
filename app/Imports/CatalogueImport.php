<?php

namespace App\Imports;

use App\Models\Catalogue;
use App\Models\CatalogueField;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class CatalogueImport implements ToCollection, WithHeadingRow
{
    protected int $adminId;
    protected array $errors = [];
    protected int $successCount = 0;
    protected int $skipCount = 0;

    public function __construct(int $adminId)
    {
        $this->adminId = $adminId;
    }

    /**
     * Process each row from Excel
     */
    public function collection(Collection $rows)
    {
        // Get catalogue fields for this admin
        $catalogueFields = CatalogueField::forTenant($this->adminId)->ordered()->get();

        if ($catalogueFields->isEmpty()) {
            $this->errors[] = "No catalogue fields configured. Please set up fields first.";
            return;
        }

        // Build field key map (Excel column name => field_key)
        $fieldMap = [];
        foreach ($catalogueFields as $field) {
            // Map both field_key and field_name to handle different Excel formats
            $fieldMap[strtolower($field->field_key)] = $field->field_key;
            $fieldMap[strtolower($field->field_name)] = $field->field_key;
            $fieldMap[strtolower(str_replace('_', ' ', $field->field_key))] = $field->field_key;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because: 1-indexed + header row

            try {
                // Map Excel columns to catalogue field keys
                $data = [];
                $hasData = false;

                foreach ($row as $excelColumn => $value) {
                    $excelColumnLower = strtolower(trim($excelColumn));

                    if (isset($fieldMap[$excelColumnLower])) {
                        $fieldKey = $fieldMap[$excelColumnLower];
                        $data[$fieldKey] = trim($value ?? '');

                        if (!empty($data[$fieldKey])) {
                            $hasData = true;
                        }
                    }
                }

                // Skip empty rows
                if (!$hasData) {
                    $this->skipCount++;
                    continue;
                }

                // Validate data against field rules
                $validationErrors = [];
                foreach ($catalogueFields as $field) {
                    $value = $data[$field->field_key] ?? '';

                    // Validate field
                    $fieldErrors = $field->validateValue($value);
                    if (!empty($fieldErrors)) {
                        $validationErrors = array_merge($validationErrors, $fieldErrors);
                    }

                    // Check uniqueness
                    if ($field->is_unique && !empty($value)) {
                        $jsonPath = '$."' . $field->field_key . '"';
                        $exists = Catalogue::where('admin_id', $this->adminId)
                            ->whereRaw("JSON_EXTRACT(data, ?) = ?", [$jsonPath, $value])
                            ->exists();

                        if ($exists) {
                            $validationErrors[] = "{$field->field_name} '{$value}' already exists";
                        }
                    }
                }

                if (!empty($validationErrors)) {
                    $this->errors[] = "Row {$rowNumber}: " . implode(', ', $validationErrors);
                    $this->skipCount++;
                    continue;
                }

                // Create catalogue entry
                Catalogue::create([
                    'admin_id' => $this->adminId,
                    'data' => $data,
                    'is_active' => true,
                ]);

                $this->successCount++;

            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $this->skipCount++;
                Log::error("Catalogue import error on row {$rowNumber}", [
                    'error' => $e->getMessage(),
                    'row_data' => $row->toArray(),
                ]);
            }
        }
    }

    /**
     * Get import results
     */
    public function getResults(): array
    {
        return [
            'success' => $this->successCount,
            'skipped' => $this->skipCount,
            'errors' => $this->errors,
            'total_processed' => $this->successCount + $this->skipCount,
        ];
    }
}
