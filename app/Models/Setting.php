<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    // Group constants
    const GROUP_GENERAL = 'general';
    const GROUP_WHATSAPP = 'whatsapp';
    const GROUP_AI = 'ai';
    const GROUP_NOTIFICATIONS = 'notifications';

    // Type constants
    const TYPE_TEXT = 'text';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';
    const TYPE_ENCRYPTED = 'encrypted';

    /**
     * Get setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $cacheKey = 'setting_' . $key;

        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return $setting->getTypedValue();
        });
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, $value, string $group = 'general', string $type = 'text'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'group' => $group,
                'type' => $type,
            ]
        );

        Cache::forget('setting_' . $key);
    }

    /**
     * Get typed value
     */
    public function getTypedValue()
    {
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);

            case self::TYPE_JSON:
                return json_decode($this->value, true);

            case self::TYPE_ENCRYPTED:
                // Don't try to decrypt empty values
                if (empty($this->value)) {
                    return '';
                }
                try {
                    return decrypt($this->value);
                } catch (\Exception $e) {
                    return ''; // Return empty if decryption fails
                }

            default:
                return $this->value;
        }
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        $settings = self::where('group', $group)->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->getTypedValue();
        }

        return $result;
    }

    /**
     * Default settings for initialization
     */
    public static function getDefaults(): array
    {
        return [
            // WhatsApp settings
            'whatsapp_api_url' => ['value' => '', 'group' => 'whatsapp', 'type' => 'text', 'description' => 'Evolution API URL'],
            'whatsapp_api_key' => ['value' => '', 'group' => 'whatsapp', 'type' => 'encrypted', 'description' => 'Evolution API Key'],
            'whatsapp_instance' => ['value' => '', 'group' => 'whatsapp', 'type' => 'text', 'description' => 'Default WhatsApp Instance'],

            // AI settings
            'ai_provider' => ['value' => 'gemini', 'group' => 'ai', 'type' => 'text', 'description' => 'AI Provider (gemini, openai, cohere)'],
            'gemini_api_key' => ['value' => '', 'group' => 'ai', 'type' => 'encrypted', 'description' => 'Google Gemini API Key'],
            'gemini_model' => ['value' => 'gemini-2.5-flash', 'group' => 'ai', 'type' => 'text', 'description' => 'Gemini Model'],

            // General settings
            'app_name' => ['value' => 'Datsun Chatbot', 'group' => 'general', 'type' => 'text', 'description' => 'Application Name'],
            'company_name' => ['value' => 'Datsun Hardware', 'group' => 'general', 'type' => 'text', 'description' => 'Company Name'],
            'sales_person_name' => ['value' => 'Rahul', 'group' => 'general', 'type' => 'text', 'description' => 'Sales Person Name for AI'],

            // Notification settings
            'escalation_enabled' => ['value' => true, 'group' => 'notifications', 'type' => 'boolean', 'description' => 'Enable Escalation Alerts'],
            'followup_days' => ['value' => 10, 'group' => 'notifications', 'type' => 'text', 'description' => 'Days before lead marked as Lose'],
        ];
    }

    /**
     * Initialize default settings
     */
    public static function initializeDefaults(): void
    {
        foreach (self::getDefaults() as $key => $config) {
            if (!self::where('key', $key)->exists()) {
                self::create([
                    'key' => $key,
                    'value' => $config['type'] === 'encrypted' && $config['value']
                        ? encrypt($config['value'])
                        : $config['value'],
                    'group' => $config['group'],
                    'type' => $config['type'],
                    'description' => $config['description'],
                ]);
            }
        }
    }
}
