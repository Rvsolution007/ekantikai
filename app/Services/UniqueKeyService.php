<?php

namespace App\Services;

use App\Models\CustomerProduct;
use App\Models\ProductQuestion;

class UniqueKeyService
{
    /**
     * Build unique key from field values based on tenant configuration
     */
    public static function buildKey(int $tenantId, array $fieldValues): string
    {
        $uniqueFields = ProductQuestion::where('tenant_id', $tenantId)
            ->where('is_unique_key', true)
            ->orderBy('unique_key_order')
            ->pluck('field_name')
            ->toArray();

        $keyParts = [];
        foreach ($uniqueFields as $field) {
            $value = $fieldValues[$field] ?? '';
            $keyParts[] = self::normalizeValue($value);
        }

        return implode('|', $keyParts);
    }

    /**
     * Build line key (includes phone number)
     */
    public static function buildLineKey(string $phone, int $tenantId, array $fieldValues): string
    {
        $uniqueKey = self::buildKey($tenantId, $fieldValues);
        return self::normalizePhone($phone) . '|' . $uniqueKey;
    }

    /**
     * Parse key back to field values
     */
    public static function parseKey(int $tenantId, string $key): array
    {
        $uniqueFields = ProductQuestion::where('tenant_id', $tenantId)
            ->where('is_unique_key', true)
            ->orderBy('unique_key_order')
            ->pluck('field_name')
            ->toArray();

        $parts = explode('|', $key);
        $result = [];

        foreach ($uniqueFields as $index => $field) {
            $result[$field] = $parts[$index] ?? '';
        }

        return $result;
    }

    /**
     * Compare two keys to see if they match a pattern (with * wildcards)
     */
    public static function matchesPattern(string $key, string $pattern): bool
    {
        $keyParts = explode('|', $key);
        $patternParts = explode('|', $pattern);

        if (count($keyParts) !== count($patternParts)) {
            return false;
        }

        foreach ($patternParts as $index => $patternPart) {
            $patternPart = trim($patternPart);

            // Empty pattern part matches anything
            if ($patternPart === '') {
                continue;
            }

            // Check for star suffix (delete/clear marker)
            if (str_ends_with($patternPart, '*')) {
                $patternPart = rtrim($patternPart, '*');
            }

            if (strtolower($patternPart) !== strtolower($keyParts[$index])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check which fields have star markers (for deletion)
     */
    public static function getStarredFields(int $tenantId, array $fieldValues): array
    {
        $starredFields = [];

        foreach ($fieldValues as $field => $value) {
            if (is_string($value) && str_ends_with($value, '*')) {
                $starredFields[$field] = rtrim($value, '*');
            }
        }

        return $starredFields;
    }

    /**
     * Check if has product/model star (delete action)
     */
    public static function hasProductModelStar(array $fieldValues): bool
    {
        $product = $fieldValues['product'] ?? $fieldValues['category'] ?? '';
        $model = $fieldValues['model'] ?? '';

        return (is_string($product) && str_ends_with($product, '*'))
            || (is_string($model) && str_ends_with($model, '*'));
    }

    /**
     * Check if has size/finish star (clear action or targeted delete)
     */
    public static function hasSizeFinishStar(array $fieldValues): bool
    {
        $size = $fieldValues['size'] ?? '';
        $finish = $fieldValues['finish'] ?? '';

        return (is_string($size) && str_ends_with($size, '*'))
            || (is_string($finish) && str_ends_with($finish, '*'));
    }

    /**
     * Normalize value for key comparison
     */
    protected static function normalizeValue($value): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        return strtolower(trim($value));
    }

    /**
     * Normalize phone number
     */
    protected static function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}
