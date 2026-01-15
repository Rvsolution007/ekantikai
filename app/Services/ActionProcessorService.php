<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerProduct;
use App\Models\QuestionnaireField;
use Illuminate\Support\Collection;

class ActionProcessorService
{
    protected int $tenantId;
    protected Customer $customer;

    public function __construct(int $tenantId, Customer $customer)
    {
        $this->tenantId = $tenantId;
        $this->customer = $customer;
    }

    /**
     * Process AI response (ConfirmMsg and RejectionMsg arrays)
     * Returns array of actions performed
     */
    public function process(array $confirmMsg, array $rejectionMsg): array
    {
        $results = [];

        // Process rejections first (delete/clear)
        foreach ($rejectionMsg as $rejection) {
            $result = $this->processRejection($rejection);
            if ($result) {
                $results[] = $result;
            }
        }

        // Process confirmations (create/update)
        foreach ($confirmMsg as $confirm) {
            $result = $this->processConfirmation($confirm);
            if ($result) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Process a single confirmation (create or update)
     */
    protected function processConfirmation(array $data): ?array
    {
        // Normalize field names to lowercase
        $fieldValues = $this->normalizeFieldNames($data);

        // Skip if no meaningful data
        if (!$this->hasMeaningfulData($fieldValues)) {
            return null;
        }

        // Clean star markers from values
        $fieldValues = $this->cleanStarMarkers($fieldValues);

        // Build unique key
        $uniqueKey = UniqueKeyService::buildKey($this->tenantId, $fieldValues);
        $lineKey = UniqueKeyService::buildLineKey($this->customer->phone, $this->tenantId, $fieldValues);

        // Find existing product
        $existing = CustomerProduct::findByUniqueKey($this->tenantId, $this->customer->id, $uniqueKey);

        if ($existing) {
            // Update existing
            $hasChanges = false;
            $existingValues = $existing->field_values ?? [];

            foreach ($fieldValues as $key => $value) {
                if ($value && $value !== ($existingValues[$key] ?? '')) {
                    $hasChanges = true;
                    break;
                }
            }

            if ($hasChanges) {
                $existing->field_values = array_merge($existingValues, array_filter($fieldValues));
                $existing->line_key = $lineKey;
                $existing->save();

                return [
                    'action' => 'update',
                    'product_id' => $existing->id,
                    'unique_key' => $uniqueKey,
                    'field_values' => $existing->field_values,
                ];
            }

            return [
                'action' => 'skip',
                'reason' => 'no_changes',
                'product_id' => $existing->id,
            ];
        }

        // Create new product
        $product = CustomerProduct::create([
            'tenant_id' => $this->tenantId,
            'customer_id' => $this->customer->id,
            'field_values' => $fieldValues,
            'unique_key' => $uniqueKey,
            'line_key' => $lineKey,
            'status' => 'pending',
        ]);

        return [
            'action' => 'create',
            'product_id' => $product->id,
            'unique_key' => $uniqueKey,
            'field_values' => $fieldValues,
        ];
    }

    /**
     * Process a single rejection (delete or clear)
     */
    protected function processRejection(array $data): ?array
    {
        $fieldValues = $this->normalizeFieldNames($data);

        // Skip if no meaningful data
        if (!$this->hasMeaningfulData($fieldValues)) {
            return null;
        }

        // Check for product/model star (DELETE)
        if (UniqueKeyService::hasProductModelStar($fieldValues)) {
            // Check for size/finish star too (DELETE2 - targeted row)
            if (UniqueKeyService::hasSizeFinishStar($fieldValues)) {
                return $this->deleteTargetedRow($fieldValues);
            }

            // DELETE all matching products
            return $this->deleteByProductModel($fieldValues);
        }

        // Check for size/finish star without product/model star (CLEAR fields)
        if (UniqueKeyService::hasSizeFinishStar($fieldValues)) {
            return $this->clearFields($fieldValues);
        }

        // Check other starred fields (CLEAR)
        $starredFields = UniqueKeyService::getStarredFields($this->tenantId, $fieldValues);
        if (!empty($starredFields)) {
            return $this->clearSpecificFields($fieldValues, $starredFields);
        }

        return null;
    }

    /**
     * Delete targeted row (DELETE2)
     */
    protected function deleteTargetedRow(array $fieldValues): ?array
    {
        $cleanValues = $this->cleanStarMarkers($fieldValues);
        $uniqueKey = UniqueKeyService::buildKey($this->tenantId, $cleanValues);

        $product = CustomerProduct::findByUniqueKey($this->tenantId, $this->customer->id, $uniqueKey);

        if ($product) {
            $product->status = 'deleted';
            $product->save();

            return [
                'action' => 'delete',
                'type' => 'targeted_row',
                'product_id' => $product->id,
                'unique_key' => $uniqueKey,
            ];
        }

        return null;
    }

    /**
     * Delete by product/model (DELETE all matching)
     */
    protected function deleteByProductModel(array $fieldValues): ?array
    {
        $cleanValues = $this->cleanStarMarkers($fieldValues);

        $query = CustomerProduct::where('tenant_id', $this->tenantId)
            ->where('customer_id', $this->customer->id)
            ->where('status', '!=', 'deleted');

        $product = $cleanValues['product'] ?? $cleanValues['category'] ?? '';
        $model = $cleanValues['model'] ?? '';

        if ($product) {
            $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(field_values, '$.product'))) = ?", [strtolower($product)]);
        }
        if ($model) {
            $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(field_values, '$.model'))) = ?", [strtolower($model)]);
        }

        $affected = $query->update(['status' => 'deleted']);

        return [
            'action' => 'delete',
            'type' => 'by_product_model',
            'affected_count' => $affected,
            'criteria' => ['product' => $product, 'model' => $model],
        ];
    }

    /**
     * Clear specific fields
     */
    protected function clearFields(array $fieldValues): ?array
    {
        $cleanValues = $this->cleanStarMarkers($fieldValues);
        $starredFields = UniqueKeyService::getStarredFields($this->tenantId, $fieldValues);

        // Find matching product
        $uniqueKey = UniqueKeyService::buildKey($this->tenantId, $cleanValues);
        $product = CustomerProduct::findByUniqueKey($this->tenantId, $this->customer->id, $uniqueKey);

        if ($product) {
            $currentValues = $product->field_values ?? [];

            foreach ($starredFields as $field => $value) {
                $currentValues[$field] = '';
            }

            $product->field_values = $currentValues;
            $product->unique_key = UniqueKeyService::buildKey($this->tenantId, $currentValues);
            $product->line_key = UniqueKeyService::buildLineKey($this->customer->phone, $this->tenantId, $currentValues);
            $product->save();

            return [
                'action' => 'clear',
                'product_id' => $product->id,
                'cleared_fields' => array_keys($starredFields),
            ];
        }

        return null;
    }

    /**
     * Clear specific starred fields
     */
    protected function clearSpecificFields(array $fieldValues, array $starredFields): ?array
    {
        return $this->clearFields($fieldValues);
    }

    /**
     * Normalize field names to lowercase
     */
    protected function normalizeFieldNames(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower($key);
            $normalized[$normalizedKey] = $value;
        }

        return $normalized;
    }

    /**
     * Clean star markers from values
     */
    protected function cleanStarMarkers(array $fieldValues): array
    {
        $cleaned = [];

        foreach ($fieldValues as $key => $value) {
            if (is_string($value)) {
                $cleaned[$key] = rtrim($value, '*');
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }

    /**
     * Check if has meaningful data
     */
    protected function hasMeaningfulData(array $fieldValues): bool
    {
        $product = $fieldValues['product'] ?? $fieldValues['category'] ?? '';
        $model = $fieldValues['model'] ?? '';
        $size = $fieldValues['size'] ?? '';
        $finish = $fieldValues['finish'] ?? '';

        return !empty($product) || !empty($model) || !empty($size) || !empty($finish);
    }
}
