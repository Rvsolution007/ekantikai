<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionTemplate extends Model
{
    protected $fillable = [
        'admin_id',
        'field_name',
        'language',
        'question_text',
        'confirmation_text',
        'error_text',
        'options_text',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // Get template for specific field and language
    public static function getTemplate(int $tenantId, string $fieldName, string $language = 'hi'): ?self
    {
        return static::where('admin_id', $tenantId)
            ->where('field_name', $fieldName)
            ->where('language', $language)
            ->first();
    }

    // Get question text with fallback to English
    public static function getQuestionText(int $tenantId, string $fieldName, string $language = 'hi'): string
    {
        $template = static::getTemplate($tenantId, $fieldName, $language);

        if (!$template && $language !== 'en') {
            $template = static::getTemplate($tenantId, $fieldName, 'en');
        }

        return $template?->question_text ?? "Please provide {$fieldName}:";
    }

    // Format confirmation message
    public function formatConfirmation(string $value): string
    {
        $text = $this->confirmation_text ?? "Noted: {value}";
        return str_replace('{value}', $value, $text);
    }

    // Format options message
    public function formatOptions(array $options): string
    {
        $text = $this->options_text ?? "Options: {options}";
        return str_replace('{options}', implode(', ', $options), $text);
    }
}
