<?php

namespace App\Services;

use App\Models\Catalogue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CatalogueMatchingService
{
    protected int $adminId;

    public function __construct(int $adminId)
    {
        $this->adminId = $adminId;
    }

    /**
     * Find matching products from catalogue based on user message
     */
    public function findMatches(string $userMessage): Collection
    {
        $keywords = $this->extractKeywords($userMessage);

        if (empty($keywords)) {
            return collect();
        }

        $catalogues = Catalogue::where('admin_id', $this->adminId)->get();
        $matches = collect();

        foreach ($catalogues as $catalogue) {
            $score = $this->calculateMatchScore($catalogue, $keywords);
            if ($score > 0) {
                $matches->push([
                    'catalogue' => $catalogue,
                    'score' => $score
                ]);
            }
        }

        // Sort by score descending and return top matches
        return $matches->sortByDesc('score')->take(5)->pluck('catalogue');
    }

    /**
     * Extract product/category keywords from message
     */
    protected function extractKeywords(string $message): array
    {
        $message = strtolower(trim($message));

        // Common words to ignore
        $stopWords = ['i', 'need', 'want', 'looking', 'for', 'chahiye', 'mujhe', 'kya', 'hai', 'the', 'a', 'an'];

        // Split by spaces and filter
        $words = explode(' ', $message);
        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array(strtolower($word), $stopWords);
        });

        return array_values($keywords);
    }

    /**
     * Calculate match score for a catalogue item
     */
    protected function calculateMatchScore(Catalogue $catalogue, array $keywords): int
    {
        $score = 0;
        $data = $catalogue->data ?? [];

        // Check category
        $category = strtolower($data['category'] ?? $catalogue->category ?? '');
        foreach ($keywords as $keyword) {
            if (str_contains($category, strtolower($keyword))) {
                $score += 10;
            }
        }

        // Check model/name
        $model = strtolower($data['model_code'] ?? $catalogue->model ?? '');
        foreach ($keywords as $keyword) {
            if (str_contains($model, strtolower($keyword))) {
                $score += 5;
            }
        }

        // Check description if available
        $description = strtolower($data['description'] ?? '');
        foreach ($keywords as $keyword) {
            if (str_contains($description, strtolower($keyword))) {
                $score += 3;
            }
        }

        return $score;
    }

    /**
     * Format product list for WhatsApp display
     */
    public function formatProductList(Collection $products): string
    {
        if ($products->isEmpty()) {
            return '';
        }

        $message = "📦 *Found these products:*\n\n";

        foreach ($products as $index => $product) {
            $data = $product->data ?? [];
            $category = $data['category'] ?? $product->category ?? 'Product';
            $model = $data['model_code'] ?? $product->model ?? 'N/A';
            $price = $data['price'] ?? 'Contact for price';

            $message .= ($index + 1) . ". *{$category}*\n";
            $message .= "   Model: {$model}\n";
            $message .= "   Price: {$price}\n\n";
        }

        $message .= "Would you like more information about any of these products?";

        return $message;
    }

    /**
     * Check if message contains product-related keywords
     */
    public function hasProductIntent(string $message): bool
    {
        $productKeywords = [
            'need',
            'want',
            'looking for',
            'chahiye',
            'price',
            'cost',
            'handle',
            'lock',
            'hinge',
            'cabinet',
            'door',
            'window',
            'hardware',
            'fitting',
            'accessory'
        ];

        $messageLower = strtolower($message);

        foreach ($productKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
