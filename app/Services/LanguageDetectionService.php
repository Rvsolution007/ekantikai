<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LanguageDetectionService
{
    /**
     * Common greetings and words by language
     */
    protected array $languagePatterns = [
        'hi' => [ // Hindi
            'नमस्ते',
            'धन्यवाद',
            'हाँ',
            'नहीं',
            'कृपया',
            'मुझे',
            'आप',
            'क्या',
            'कैसे',
            'हम',
            'मैं',
            'है',
            'और',
            'को',
            'से',
            'का',
            'की',
            'के',
            'में',
            'यह',
            'वह',
        ],
        'hinglish' => [ // Hindi + English mix patterns
            'mujhe',
            'chahiye',
            'batao',
            'kya',
            'hai',
            'nahi',
            'haan',
            'kaise',
            'kitna',
            'acha',
            'theek',
            'sahi',
            'galat',
            'karo',
            'karna',
            'dena',
            'lena',
            'bolo',
            'bta',
            'kr',
            'ho',
            'toh',
            'ye',
            'wo',
            'bol',
            'de',
            'le',
            'bhai',
            'ji',
            'aap',
            'tum',
            'hum',
            'main',
            'mera',
            'tera',
            'uska',
            'iski',
            'wala',
        ],
        'en' => [ // English
            'hello',
            'hi',
            'thank',
            'please',
            'yes',
            'no',
            'want',
            'need',
            'how',
            'what',
            'where',
            'when',
            'why',
            'which',
            'can',
            'could',
            'would',
            'should',
            'will',
        ],
        'gu' => [ // Gujarati
            'નમસ્તે',
            'આભાર',
            'હા',
            'ના',
            'કૃપા',
            'મને',
            'તમે',
            'શું',
            'કેવી',
            'અમે',
        ],
        'mr' => [ // Marathi
            'नमस्कार',
            'धन्यवाद',
            'होय',
            'नाही',
            'कृपया',
            'मला',
            'तुम्ही',
            'काय',
            'कसे',
            'आम्ही',
        ],
        'ta' => [ // Tamil
            'வணக்கம்',
            'நன்றி',
            'ஆம்',
            'இல்லை',
            'தயவுசெய்து',
            'எனக்கு',
            'நீங்கள்',
            'என்ன',
            'எப்படி',
        ],
        'te' => [ // Telugu
            'నమస్కారం',
            'ధన్యవాదాలు',
            'అవును',
            'కాదు',
            'దయచేసి',
            'నాకు',
            'మీరు',
            'ఏమిటి',
            'ఎలా',
        ],
        'bn' => [ // Bengali
            'নমস্কার',
            'ধন্যবাদ',
            'হ্যাঁ',
            'না',
            'দয়া করে',
            'আমাকে',
            'আপনি',
            'কি',
            'কেমন',
        ],
        'pa' => [ // Punjabi
            'ਸਤ ਸ੍ਰੀ ਅਕਾਲ',
            'ਧੰਨਵਾਦ',
            'ਹਾਂ',
            'ਨਹੀਂ',
            'ਕਿਰਪਾ',
            'ਮੈਨੂੰ',
            'ਤੁਸੀਂ',
            'ਕੀ',
            'ਕਿਵੇਂ',
        ],
    ];

    /**
     * Detect language from message text
     */
    public function detect(string $message): string
    {
        $message = mb_strtolower(trim($message));
        $scores = [];

        // Check for non-ASCII characters (indicates non-English script)
        $hasNonAscii = preg_match('/[^\x00-\x7F]/', $message);

        foreach ($this->languagePatterns as $lang => $patterns) {
            $score = 0;
            foreach ($patterns as $pattern) {
                if (mb_strpos($message, mb_strtolower($pattern)) !== false) {
                    $score++;
                }
            }
            $scores[$lang] = $score;
        }

        // Find highest scoring language
        arsort($scores);
        $topLang = array_key_first($scores);
        $topScore = $scores[$topLang] ?? 0;

        // If no clear match, use default based on script detection
        if ($topScore === 0) {
            if ($hasNonAscii) {
                // Try to detect script
                if (preg_match('/[\x{0900}-\x{097F}]/u', $message)) {
                    return 'hi'; // Devanagari script
                }
                if (preg_match('/[\x{0A80}-\x{0AFF}]/u', $message)) {
                    return 'gu'; // Gujarati script
                }
                if (preg_match('/[\x{0B80}-\x{0BFF}]/u', $message)) {
                    return 'ta'; // Tamil script
                }
                if (preg_match('/[\x{0C00}-\x{0C7F}]/u', $message)) {
                    return 'te'; // Telugu script
                }
                if (preg_match('/[\x{0980}-\x{09FF}]/u', $message)) {
                    return 'bn'; // Bengali script
                }
                if (preg_match('/[\x{0A00}-\x{0A7F}]/u', $message)) {
                    return 'pa'; // Punjabi script
                }
            }
            return 'en'; // Default to English
        }

        // Check for Hinglish (mix of Hindi words with English structure)
        if ($topLang === 'hinglish' || ($topLang === 'hi' && $this->hasEnglishWords($message))) {
            return 'hinglish';
        }

        return $topLang;
    }

    /**
     * Check if message contains English words
     */
    protected function hasEnglishWords(string $message): bool
    {
        $englishWords = ['want', 'need', 'please', 'ok', 'okay', 'yes', 'no', 'thanks', 'thank', 'hello', 'hi', 'bye'];
        $message = mb_strtolower($message);

        foreach ($englishWords as $word) {
            if (str_contains($message, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get language name for display
     */
    public function getLanguageName(string $code): string
    {
        return match ($code) {
            'hi' => 'Hindi',
            'hinglish' => 'Hinglish',
            'en' => 'English',
            'gu' => 'Gujarati',
            'mr' => 'Marathi',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'bn' => 'Bengali',
            'pa' => 'Punjabi',
            default => 'English',
        };
    }

    /**
     * Get instruction for AI to respond in detected language
     */
    public function getLanguageInstruction(string $code): string
    {
        return match ($code) {
            'hi' => 'Respond in Hindi (Devanagari script).',
            'hinglish' => 'Respond in Hinglish (Hindi words written in English/Roman script, mixed with English). This is casual and friendly.',
            'en' => 'Respond in English.',
            'gu' => 'Respond in Gujarati (Gujarati script).',
            'mr' => 'Respond in Marathi (Devanagari script).',
            'ta' => 'Respond in Tamil (Tamil script).',
            'te' => 'Respond in Telugu (Telugu script).',
            'bn' => 'Respond in Bengali (Bengali script).',
            'pa' => 'Respond in Punjabi (Gurmukhi script).',
            default => 'Respond in the same language as the user.',
        };
    }
}
