<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadProduct;
use App\Models\QuestionnaireField;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProductConfirmationService
{
    /**
     * Get unique key fields for admin (ordered by unique_key_order)
     */
    public function getUniqueKeyFields(int $adminId): Collection
    {
        return QuestionnaireField::where('admin_id', $adminId)
            ->where('is_active', true)
            ->where('is_unique_key', true)
            ->orderBy('unique_key_order')
            ->get(['field_name', 'display_name', 'unique_key_order', 'is_unique_field']);
    }

    /**
     * Get all product fields for admin
     */
    public function getAllProductFields(int $adminId): Collection
    {
        return QuestionnaireField::where('admin_id', $adminId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['field_name', 'display_name', 'is_unique_key', 'unique_key_order', 'is_unique_field']);
    }

    /**
     * Get the unique field (e.g., model)
     */
    public function getUniqueField(int $adminId): ?QuestionnaireField
    {
        return QuestionnaireField::where('admin_id', $adminId)
            ->where('is_active', true)
            ->where('is_unique_field', true)
            ->first();
    }

    /**
     * Build unique key from product data based on admin's unique key fields
     */
    public function buildUniqueKey(int $leadId, array $productData, int $adminId): string
    {
        $uniqueFields = $this->getUniqueKeyFields($adminId);

        $keyParts = [$leadId];
        foreach ($uniqueFields as $field) {
            $value = $this->normalizeValue(
                $field->field_name,
                $productData[$field->field_name] ?? ''
            );
            $keyParts[] = strtolower(trim($value));
        }

        return implode('|', $keyParts);
    }

    /**
     * Normalize product value (e.g., "cabinet handle" → "Cabinet handles")
     */
    public function normalizeValue(string $fieldName, ?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        $value = trim($value);

        // Normalize category/product type
        if (in_array($fieldName, ['category', 'product', 'product_type'])) {
            $lower = strtolower($value);
            if (str_contains($lower, 'cabinet handle'))
                return 'Cabinet handles';
            if (str_contains($lower, 'profile handle'))
                return 'Profile handles';
            if (str_contains($lower, 'knob handle'))
                return 'Knob handles';
            if (str_contains($lower, 'wardrobe handle'))
                return 'Wardrobe handles';
            if (str_contains($lower, 'main') && str_contains($lower, 'door'))
                return 'Main door handles';
        }

        return $value;
    }

    /**
     * Process AI confirmations - CREATE, UPDATE, or SKIP
     */
    public function processConfirmations(Lead $lead, array $confirmations): array
    {
        $results = [];
        $adminId = $lead->admin_id;

        Log::debug('ProductConfirmationService: Processing confirmations', [
            'lead_id' => $lead->id,
            'count' => count($confirmations),
        ]);

        foreach ($confirmations as $confirmation) {
            // Skip empty confirmations
            if (!$this->isMeaningfulConfirmation($confirmation)) {
                continue;
            }

            // Normalize the data
            $normalized = $this->normalizeConfirmation($confirmation, $adminId);

            // Build unique key for this confirmation
            $uniqueKey = $this->buildUniqueKey($lead->id, $normalized, $adminId);

            // Find existing product by unique key OR partial match
            $existing = $this->findMatchingProduct($lead, $normalized, $uniqueKey);

            if ($existing) {
                // UPDATE existing row
                $result = $this->updateProduct($existing, $normalized, $uniqueKey);
                $results[] = ['action' => 'update', 'product' => $result];
            } else {
                // Check for partial match (to fill blanks)
                $partialMatch = $this->findPartialMatch($lead, $normalized);

                if ($partialMatch) {
                    // Fill blanks in existing row
                    $result = $this->fillBlanks($partialMatch, $normalized);
                    $results[] = ['action' => 'update_fill', 'product' => $result];
                } else {
                    // CREATE new row
                    $result = $this->createProduct($lead, $normalized, $uniqueKey);
                    $results[] = ['action' => 'create', 'product' => $result];
                }
            }
        }

        return $results;
    }

    /**
     * Process AI rejections - DELETE or CLEAR
     */
    public function processRejections(Lead $lead, array $rejections): array
    {
        $results = [];
        $adminId = $lead->admin_id;
        $uniqueFields = $this->getUniqueKeyFields($adminId);
        $uniqueFieldInfo = $this->getUniqueField($adminId);

        foreach ($rejections as $rejection) {
            if (empty($rejection))
                continue;

            // Check which fields are specified for deletion (have * or value)
            $specifiedFields = $this->getSpecifiedRejectionFields($rejection, $uniqueFields);

            // Determine if we should DELETE or CLEAR
            $shouldDelete = $this->shouldDeleteRow($specifiedFields, $uniqueFields, $uniqueFieldInfo);

            if ($shouldDelete) {
                // DELETE entire row
                $result = $this->deleteMatchingProduct($lead, $rejection);
                if ($result) {
                    $results[] = ['action' => 'delete', 'product' => $result];
                }
            } else {
                // CLEAR specific fields only
                $result = $this->clearFields($lead, $rejection, $specifiedFields);
                if ($result) {
                    $results[] = ['action' => 'clear', 'product' => $result];
                }
            }
        }

        return $results;
    }

    /**
     * Check if confirmation has meaningful data
     * Requires at least 2 fields to be filled to create a product row
     * This prevents creating incomplete rows when user only mentions category
     */
    protected function isMeaningfulConfirmation(array $confirmation): bool
    {
        $fields = ['category', 'model', 'size', 'finish', 'material', 'packaging'];
        $filledCount = 0;

        foreach ($fields as $field) {
            $value = $confirmation[$field] ?? null;
            if (!empty($value) && $value !== '-' && $value !== null) {
                $filledCount++;
            }
        }

        // Require at least 2 fields (e.g., category + model, or category + size)
        // This prevents creating rows when user just says "Profile handles"
        return $filledCount >= 2;
    }

    /**
     * Normalize confirmation data
     */
    protected function normalizeConfirmation(array $confirmation, int $adminId): array
    {
        $fields = $this->getAllProductFields($adminId);
        $normalized = [];

        foreach ($fields as $field) {
            $fieldName = $field->field_name;
            $value = $confirmation[$fieldName] ?? $confirmation[strtolower($fieldName)] ?? null;

            if ($value !== null && $value !== '' && $value !== '-') {
                $normalized[$fieldName] = $this->normalizeValue($fieldName, $value);
            }
        }

        // Handle qty separately - only set if user mentioned it
        $qtyValue = $confirmation['qty'] ?? $confirmation['quantity'] ?? null;
        if ($qtyValue !== null && $qtyValue !== '' && $qtyValue !== 0) {
            $normalized['qty'] = intval($qtyValue);
            if ($normalized['qty'] < 1) {
                $normalized['qty'] = null; // Don't default to 1
            }
        }

        return $normalized;
    }

    /**
     * Find existing product by exact unique key match
     */
    protected function findMatchingProduct(Lead $lead, array $data, string $uniqueKey): ?LeadProduct
    {
        // First try exact unique key match
        $product = LeadProduct::where('lead_id', $lead->id)
            ->where('unique_key', $uniqueKey)
            ->first();

        return $product;
    }

    /**
     * Find partial match (same product, can fill blanks)
     */
    protected function findPartialMatch(Lead $lead, array $data): ?LeadProduct
    {
        $adminId = $lead->admin_id;
        $uniqueFields = $this->getUniqueKeyFields($adminId);

        // Build query for partial match
        $query = LeadProduct::where('lead_id', $lead->id);

        // Match on filled unique key fields, allow blanks to be filled
        $hasPartialMatch = false;

        foreach ($uniqueFields as $field) {
            $fieldName = $field->field_name;
            $newValue = $data[$fieldName] ?? '';

            if (!empty($newValue)) {
                // This field has a value in new data
                // Match rows where this field is same OR blank
                $query->where(function ($q) use ($fieldName, $newValue) {
                    $q->where($fieldName, strtolower($newValue))
                        ->orWhereNull($fieldName)
                        ->orWhere($fieldName, '');
                });
                $hasPartialMatch = true;
            }
        }

        if (!$hasPartialMatch) {
            return null;
        }

        // Get candidates and find one with blanks to fill
        $candidates = $query->get();

        foreach ($candidates as $candidate) {
            // Check if we can fill any blanks
            foreach ($uniqueFields as $field) {
                $fieldName = $field->field_name;
                $existingValue = $candidate->getFieldValue($fieldName);
                $newValue = $data[$fieldName] ?? '';

                if (empty($existingValue) && !empty($newValue)) {
                    // Found a blank that can be filled
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * Update existing product
     */
    protected function updateProduct(LeadProduct $product, array $data, string $uniqueKey): LeadProduct
    {
        $updates = [];

        foreach ($data as $field => $value) {
            if ($value !== null && $value !== '') {
                $updates[$field] = $value;
            }
        }

        $updates['unique_key'] = $uniqueKey;
        $updates['confirmed_at'] = now();

        $product->update($updates);

        Log::debug('ProductConfirmationService: Updated product', [
            'product_id' => $product->id,
            'updates' => $updates,
        ]);

        return $product->fresh();
    }

    /**
     * Fill blanks in existing product
     */
    protected function fillBlanks(LeadProduct $product, array $data): LeadProduct
    {
        $updates = [];

        foreach ($data as $field => $value) {
            $existingValue = $product->getFieldValue($field);

            // Only fill if existing is blank and new has value
            if (empty($existingValue) && !empty($value)) {
                $updates[$field] = $value;
            }
        }

        if (!empty($updates)) {
            // Rebuild unique key after filling blanks
            $newData = array_merge($product->toProductArray(), $updates);
            $updates['unique_key'] = $this->buildUniqueKey(
                $product->lead_id,
                $newData,
                $product->admin_id
            );
            $updates['confirmed_at'] = now();

            $product->update($updates);

            Log::debug('ProductConfirmationService: Filled blanks', [
                'product_id' => $product->id,
                'updates' => $updates,
            ]);
        }

        return $product->fresh();
    }

    /**
     * Create new product
     */
    protected function createProduct(Lead $lead, array $data, string $uniqueKey): LeadProduct
    {
        $productData = [
            'lead_id' => $lead->id,
            'admin_id' => $lead->admin_id,
            'unique_key' => $uniqueKey,
            'confirmed_at' => now(),
            // qty will be set from $data if user mentioned it
        ];

        foreach ($data as $field => $value) {
            if ($value !== null && $value !== '') {
                $productData[$field] = $value;
            }
        }

        $product = LeadProduct::create($productData);

        Log::debug('ProductConfirmationService: Created product', [
            'product_id' => $product->id,
            'data' => $productData,
        ]);

        return $product;
    }

    /**
     * Get fields specified in rejection
     */
    protected function getSpecifiedRejectionFields(array $rejection, Collection $uniqueFields): array
    {
        $specified = [];

        foreach ($uniqueFields as $field) {
            $fieldName = $field->field_name;
            $value = $rejection[$fieldName] ?? null;

            if (!empty($value)) {
                // Check for star marker
                $hasStar = str_contains($value, '*');
                $cleanValue = str_replace('*', '', $value);

                $specified[$fieldName] = [
                    'value' => $cleanValue,
                    'has_star' => $hasStar,
                    'order' => $field->unique_key_order,
                    'is_unique_field' => $field->is_unique_field,
                ];
            }
        }

        return $specified;
    }

    /**
     * Determine if row should be deleted based on specified fields
     * DELETE = unique_field is specified + all unique_keys up to its order
     */
    protected function shouldDeleteRow(array $specifiedFields, Collection $uniqueFields, ?QuestionnaireField $uniqueFieldInfo): bool
    {
        if (!$uniqueFieldInfo) {
            return false;
        }

        $uniqueFieldName = $uniqueFieldInfo->field_name;

        // Check if unique_field is specified
        if (!isset($specifiedFields[$uniqueFieldName])) {
            return false;
        }

        $uniqueFieldOrder = $specifiedFields[$uniqueFieldName]['order'] ?? 999;

        // Check if all unique_keys up to and including unique_field's order are specified
        foreach ($uniqueFields as $field) {
            if ($field->unique_key_order <= $uniqueFieldOrder) {
                if (!isset($specifiedFields[$field->field_name])) {
                    // Missing a required field for deletion
                    return false;
                }
            }
        }

        // All required fields are specified → DELETE
        return true;
    }

    /**
     * Delete matching product
     */
    protected function deleteMatchingProduct(Lead $lead, array $rejection): ?array
    {
        $adminId = $lead->admin_id;
        $query = LeadProduct::where('lead_id', $lead->id);

        $uniqueFields = $this->getUniqueKeyFields($adminId);

        foreach ($uniqueFields as $field) {
            $fieldName = $field->field_name;
            $value = $rejection[$fieldName] ?? null;

            if (!empty($value)) {
                $cleanValue = strtolower(str_replace('*', '', $value));
                $cleanValue = $this->normalizeValue($fieldName, $cleanValue);
                $query->where($fieldName, strtolower($cleanValue));
            }
        }

        $product = $query->first();

        if ($product) {
            $deletedData = $product->toArray();
            $product->delete();

            Log::debug('ProductConfirmationService: Deleted product', [
                'product' => $deletedData,
            ]);

            return $deletedData;
        }

        return null;
    }

    /**
     * Clear specific fields only
     */
    protected function clearFields(Lead $lead, array $rejection, array $specifiedFields): ?LeadProduct
    {
        $adminId = $lead->admin_id;
        $uniqueFields = $this->getUniqueKeyFields($adminId);

        // Build query to find the product
        $query = LeadProduct::where('lead_id', $lead->id);

        foreach ($uniqueFields as $field) {
            $fieldName = $field->field_name;
            $spec = $specifiedFields[$fieldName] ?? null;

            if ($spec && !empty($spec['value'])) {
                $cleanValue = strtolower($this->normalizeValue($fieldName, $spec['value']));
                $query->where($fieldName, $cleanValue);
            }
        }

        $product = $query->first();

        if ($product) {
            $updates = [];

            // Clear fields that have star marker
            foreach ($specifiedFields as $fieldName => $spec) {
                if ($spec['has_star']) {
                    $updates[$fieldName] = null;
                }
            }

            if (!empty($updates)) {
                // Also clear non-unique-key fields if specified with star
                foreach ($rejection as $fieldName => $value) {
                    if (str_contains($value ?? '', '*')) {
                        $updates[$fieldName] = null;
                    }
                }

                $product->update($updates);

                Log::debug('ProductConfirmationService: Cleared fields', [
                    'product_id' => $product->id,
                    'cleared' => array_keys($updates),
                ]);
            }

            return $product->fresh();
        }

        return null;
    }
}
