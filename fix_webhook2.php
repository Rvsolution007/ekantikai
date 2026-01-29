<?php
// Fix script for getFieldOptionsFromCatalogue - with better logging

$file = __DIR__ . '/app/Http/Controllers/Api/WebhookController.php';
$content = file_get_contents($file);

// Find and replace the getFieldOptionsFromCatalogue method
$oldMethod = '    protected function getFieldOptionsFromCatalogue(int $adminId, string $fieldName, Lead $lead): array
    {
        // Get collected data to apply filters
        $collectedData = $lead->collected_data ?? [];
        $workflowAnswers = $collectedData[\'workflow_questions\'] ?? [];

        // Build catalogue query
        $query = \App\Models\Catalogue::where(\'admin_id\', $adminId)
            ->where(\'is_active\', true);

        // Apply progressive filters based on already-answered fields
        foreach ($workflowAnswers as $key => $value) {
            if (!empty($value)) {
                // Split combined values (e.g., "Knob handles, Profile handles")
                $splitPattern = \'/\\s*(?:,|\\s+or\\s+|\\s+and\\s+|\\s+aur\\s+)\\s*/i\';
                $values = preg_split($splitPattern, $value);
                $values = array_filter(array_map(\'trim\', $values));
                
                if (count($values) > 1) {
                    // Multiple values - use OR conditions
                    $query->where(function ($q) use ($key, $values) {
                        $jsonPath = \'$."' . '\' . $key . \'"' . '\' . \'\';
                        foreach ($values as $idx => $singleValue) {
                            if ($idx === 0) {
                                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$singleValue}%"]);
                            } else {
                                $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$singleValue}%"]);
                            }
                        }
                    });
                } else {
                    // Single value
                    $query->where(function ($q) use ($key, $value) {
                        $jsonPath = \'$."' . '\' . $key . \'"' . '\' . \'\';
                        $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$value}%"]);
                    });
                }
            }
        }

        // Get catalogue items
        $items = $query->get();';

$newMethod = '    protected function getFieldOptionsFromCatalogue(int $adminId, string $fieldName, Lead $lead): array
    {
        // Get collected data to apply filters
        $collectedData = $lead->collected_data ?? [];
        $workflowAnswers = $collectedData[\'workflow_questions\'] ?? [];

        // Build catalogue query
        $query = \App\Models\Catalogue::where(\'admin_id\', $adminId)
            ->where(\'is_active\', true);

        // Apply progressive filters based on already-answered fields
        foreach ($workflowAnswers as $key => $value) {
            if (!empty($value)) {
                // Split combined values (e.g., "Knob handles, Profile handles")
                $splitPattern = \'/\\s*(?:,|\\s+or\\s+|\\s+and\\s+|\\s+aur\\s+)\\s*/i\';
                $values = preg_split($splitPattern, $value);
                $values = array_filter(array_map(\'trim\', $values));
                
                Log::debug(\'getFieldOptionsFromCatalogue: Processing filter\', [
                    \'field\' => $key,
                    \'original_value\' => $value,
                    \'split_values\' => $values,
                    \'count\' => count($values),
                ]);
                
                if (count($values) > 1) {
                    // Multiple values - use OR conditions
                    $query->where(function ($q) use ($key, $values) {
                        foreach ($values as $idx => $singleValue) {
                            $jsonPath = \'$."\'.$key.\'"\';
                            if ($idx === 0) {
                                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$singleValue}%"]);
                            } else {
                                $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$singleValue}%"]);
                            }
                        }
                    });
                } else {
                    // Single value
                    $query->where(function ($q) use ($key, $value) {
                        $jsonPath = \'$."\'.$key.\'"\';
                        $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$value}%"]);
                    });
                }
            }
        }

        // Get catalogue items
        $items = $query->get();
        
        Log::debug(\'getFieldOptionsFromCatalogue: Query results\', [
            \'admin_id\' => $adminId,
            \'field_requested\' => $fieldName,
            \'workflow_answers\' => $workflowAnswers,
            \'items_found\' => $items->count(),
        ]);';

if (strpos($content, 'getFieldOptionsFromCatalogue: Processing filter') !== false) {
    echo "Already fixed with logging.\n";
} else {
    // Use simple string replacement on the function signature and first few lines
    $oldStart = '    protected function getFieldOptionsFromCatalogue(int $adminId, string $fieldName, Lead $lead): array
    {
        // Get collected data to apply filters
        $collectedData = $lead->collected_data ?? [];
        $workflowAnswers = $collectedData[\'workflow_questions\'] ?? [];

        // Build catalogue query
        $query = \App\Models\Catalogue::where(\'admin_id\', $adminId)
            ->where(\'is_active\', true);

        // Apply progressive filters based on already-answered fields
        foreach ($workflowAnswers as $key => $value) {
            if (!empty($value)) {
                // Split combined values';

    $newStart = '    protected function getFieldOptionsFromCatalogue(int $adminId, string $fieldName, Lead $lead): array
    {
        // Get collected data to apply filters
        $collectedData = $lead->collected_data ?? [];
        $workflowAnswers = $collectedData[\'workflow_questions\'] ?? [];

        // Build catalogue query
        $query = \App\Models\Catalogue::where(\'admin_id\', $adminId)
            ->where(\'is_active\', true);

        // Apply progressive filters based on already-answered fields
        foreach ($workflowAnswers as $key => $value) {
            if (!empty($value)) {
                // Split combined values (e.g., "Knob handles, Profile handles")
                $splitPattern = \'/\\s*(?:,|\\s+or\\s+|\\s+and\\s+|\\s+aur\\s+)\\s*/i\';
                $values = preg_split($splitPattern, $value);
                $values = array_filter(array_map(\'trim\', $values));
                
                Log::debug(\'getFieldOptionsFromCatalogue: Processing filter\', [
                    \'field\' => $key,
                    \'original_value\' => $value,
                    \'split_values\' => $values,
                    \'count\' => count($values),
                ]);
                
                if (count($values) > 1) {
                    // Multiple values - use OR conditions  
                    $query->where(function ($q) use ($key, $values) {
                        foreach ($values as $idx => $singleValue) {
                            $jsonPath = \'$."\'.$key.\'"\';
                            if ($idx === 0) {
                                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$singleValue}%"]);
                            } else {
                                $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$singleValue}%"]);
                            }
                        }
                    });
                } else {
                    // Single value
                    $query->where(function ($q) use ($key, $value) {
                        $jsonPath = \'$."\'.$key.\'"\';
                        $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$value}%"]);
                    });
                }
            }
        }

        // Get catalogue items
        $items = $query->get();
        
        Log::debug(\'getFieldOptionsFromCatalogue: Query results\', [
            \'admin_id\' => $adminId,
            \'field_requested\' => $fieldName,
            \'workflow_answers\' => $workflowAnswers,
            \'items_found\' => $items->count(),
        ]);
        
        // CRITICAL FIX: Skip - must rewrite getFieldOptionsFromCatalogue completely
                // Skip combined values';

    // Just look for "// Split combined values" and add logging after split
    if (strpos($content, 'Split combined values') !== false) {
        echo "Found split line - needs smarter approach.\n";

        // Let's do line by line replacement
        $lines = explode("\n", $content);
        $newLines = [];
        $inMethod = false;
        $addedLogging1 = false;
        $foundItemsGet = false;

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $newLines[] = $line;

            // After split values array_filter, add logging
            if (strpos($line, '$values = array_filter(array_map(\'trim\'') !== false && !$addedLogging1) {
                $newLines[] = '                ';
                $newLines[] = '                Log::debug(\'getFieldOptionsFromCatalogue: Processing filter\', [';
                $newLines[] = '                    \'field\' => $key,';
                $newLines[] = '                    \'original_value\' => $value,';
                $newLines[] = '                    \'split_values\' => $values,';
                $newLines[] = '                    \'count\' => count($values),';
                $newLines[] = '                ]);';
                $addedLogging1 = true;
            }

            // After $items = $query->get(); add logging
            if (strpos($line, '$items = $query->get()') !== false && strpos($line, 'getFieldOptionsFromCatalogue') === false) {
                // Check if next line already has logging
                if (!isset($lines[$i + 1]) || strpos($lines[$i + 1], 'Log::debug') === false) {
                    $newLines[] = '';
                    $newLines[] = '        Log::debug(\'getFieldOptionsFromCatalogue: Query results\', [';
                    $newLines[] = '            \'admin_id\' => $adminId,';
                    $newLines[] = '            \'field_requested\' => $fieldName,';
                    $newLines[] = '            \'workflow_answers\' => $workflowAnswers,';
                    $newLines[] = '            \'items_found\' => $items->count(),';
                    $newLines[] = '        ]);';
                    $foundItemsGet = true;
                }
            }
        }

        if ($addedLogging1 || $foundItemsGet) {
            file_put_contents($file, implode("\n", $newLines));
            echo "Added logging. addedLogging1=$addedLogging1, foundItemsGet=$foundItemsGet\n";
        } else {
            echo "Could not find injection points.\n";
        }
    }
}
