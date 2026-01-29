<?php
// Fix script for getFieldOptionsFromCatalogue method

$file = __DIR__ . '/app/Http/Controllers/Api/WebhookController.php';
$content = file_get_contents($file);

// Old code pattern
$oldCode = '        foreach ($workflowAnswers as $key => $value) {
            if (!empty($value)) {
                // Try to filter by this field in JSON data
                $query->where(function ($q) use ($key, $value) {
                    $jsonPath = \'$."' . '\' . $key . \'' . '"' . '\';
                    $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$value}%"]);
                });
            }
        }';

// New code with combined value handling
$newCode = '        foreach ($workflowAnswers as $key => $value) {
            if (!empty($value)) {
                // Split combined values (e.g., "Knob handles, Profile handles")
                $splitPattern = \'/\\s*(?:,|\\s+or\\s+|\\s+and\\s+|\\s+aur\\s+)\\s*/i\';
                $values = preg_split($splitPattern, $value);
                $values = array_filter(array_map(\'trim\', $values));
                
                if (count($values) > 1) {
                    // Multiple values - use OR conditions
                    $query->where(function ($q) use ($key, $values) {
                        $jsonPath = \'$."' . '\' . $key . \'' . '"' . '\';
                        foreach ($values as $i => $singleValue) {
                            if ($i === 0) {
                                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$singleValue}%"]);
                            } else {
                                $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$singleValue}%"]);
                            }
                        }
                    });
                } else {
                    // Single value
                    $query->where(function ($q) use ($key, $value) {
                        $jsonPath = \'$."' . '\' . $key . \'' . '"' . '\';
                        $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$value}%"]);
                    });
                }
            }
        }';

// Check if old code exists
if (strpos($content, 'Try to filter by this field in JSON data') !== false) {
    echo "Found old code, replacing...\n";

    // Replace using line-by-line approach
    $lines = explode("\n", $content);
    $newLines = [];
    $skip = false;
    $skipCount = 0;

    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];

        if (strpos($line, 'Try to filter by this field in JSON data') !== false) {
            // Found the target, skip old code block and insert new
            $newLines[] = '                // Split combined values (e.g., "Knob handles, Profile handles")';
            $newLines[] = '                $splitPattern = \'/\\s*(?:,|\\s+or\\s+|\\s+and\\s+|\\s+aur\\s+)\\s*/i\';';
            $newLines[] = '                $values = preg_split($splitPattern, $value);';
            $newLines[] = '                $values = array_filter(array_map(\'trim\', $values));';
            $newLines[] = '                ';
            $newLines[] = '                if (count($values) > 1) {';
            $newLines[] = '                    // Multiple values - use OR conditions';
            $newLines[] = '                    $query->where(function ($q) use ($key, $values) {';
            $newLines[] = '                        $jsonPath = \'$."' . '\' . $key . \'' . '"\' . \'\';';
            $newLines[] = '                        foreach ($values as $idx => $singleValue) {';
            $newLines[] = '                            if ($idx === 0) {';
            $newLines[] = '                                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$singleValue}%"]);';
            $newLines[] = '                            } else {';
            $newLines[] = '                                $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$singleValue}%"]);';
            $newLines[] = '                            }';
            $newLines[] = '                        }';
            $newLines[] = '                    });';
            $newLines[] = '                } else {';
            $newLines[] = '                    // Single value';
            $newLines[] = '                    $query->where(function ($q) use ($key, $value) {';
            $newLines[] = '                        $jsonPath = \'$."' . '\' . $key . \'' . '"\' . \'\';';
            $newLines[] = '                        $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$value}%"]);';
            $newLines[] = '                    });';
            $newLines[] = '                }';

            // Skip old code lines (next 4 lines)
            $skip = true;
            $skipCount = 4;
            continue;
        }

        if ($skip && $skipCount > 0) {
            $skipCount--;
            if ($skipCount == 0)
                $skip = false;
            continue;
        }

        $newLines[] = $line;
    }

    file_put_contents($file, implode("\n", $newLines));
    echo "Done! File updated.\n";
} else {
    echo "Old code not found or already fixed.\n";
}
