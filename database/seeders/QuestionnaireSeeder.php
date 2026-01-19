<?php

namespace Database\Seeders;

use App\Models\ProductQuestion;
use App\Models\GlobalQuestion;
use App\Models\QuestionTemplate;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class QuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        // Get first admin or create one
        $admin = Admin::first();
        if (!$admin) {
            $admin = Admin::create([
                'name' => 'Datsun Hardware',
                'email' => 'admin@datsun.com',
                'is_active' => true,
            ]);
        }

        $adminId = $admin->id;

        // Create Questionnaire Fields (Datsun Hardware Default)
        $fields = [
            [
                'field_name' => 'category',
                'display_name' => 'Product Category',
                'field_type' => 'select',
                'is_required' => true,
                'sort_order' => 1,
                'is_unique_key' => true,
                'unique_key_order' => 1,
                'options_source' => 'catalogue',
                'catalogue_field' => 'category',
            ],
            [
                'field_name' => 'model',
                'display_name' => 'Model Number',
                'field_type' => 'select',
                'is_required' => true,
                'sort_order' => 2,
                'is_unique_key' => true,
                'unique_key_order' => 2,
                'options_source' => 'catalogue',
                'catalogue_field' => 'model_code',
            ],
            [
                'field_name' => 'size',
                'display_name' => 'Size',
                'field_type' => 'select',
                'is_required' => false,
                'sort_order' => 3,
                'is_unique_key' => true,
                'unique_key_order' => 3,
                'options_source' => 'catalogue',
                'catalogue_field' => 'sizes',
            ],
            [
                'field_name' => 'finish',
                'display_name' => 'Finish/Color',
                'field_type' => 'select',
                'is_required' => false,
                'sort_order' => 4,
                'is_unique_key' => true,
                'unique_key_order' => 4,
                'options_source' => 'catalogue',
                'catalogue_field' => 'finishes',
            ],
            [
                'field_name' => 'qty',
                'display_name' => 'Quantity',
                'field_type' => 'number',
                'is_required' => true,
                'sort_order' => 5,
                'is_unique_key' => false,
                'options_source' => 'manual',
                'validation_rules' => ['min' => 1, 'max' => 100000],
            ],
            [
                'field_name' => 'packaging',
                'display_name' => 'Packaging',
                'field_type' => 'select',
                'is_required' => false,
                'sort_order' => 6,
                'is_unique_key' => false,
                'options_source' => 'manual',
                'options_manual' => ['Standard Box', 'Bulk', 'Custom'],
            ],
            [
                'field_name' => 'material',
                'display_name' => 'Material',
                'field_type' => 'select',
                'is_required' => false,
                'sort_order' => 7,
                'is_unique_key' => false,
                'options_source' => 'manual',
                'options_manual' => ['Aluminium', 'Steel', 'Brass', 'Zinc'],
            ],
        ];

        foreach ($fields as $field) {
            ProductQuestion::updateOrCreate(
                ['admin_id' => $adminId, 'field_name' => $field['field_name']],
                array_merge($field, ['admin_id' => $adminId, 'is_active' => true])
            );
        }

        // Create Global Questions
        $globalQuestions = [
            [
                'field_name' => 'city',
                'display_name' => 'City',
                'field_type' => 'text',
                'trigger_position' => 'first',
                'is_required' => false,
                'sort_order' => 1,
            ],
            [
                'field_name' => 'purpose_of_purchase',
                'display_name' => 'Purpose of Purchase',
                'field_type' => 'select',
                'options' => ['Wholesale', 'Retail'],
                'trigger_position' => 'after_field',
                'trigger_after_field' => 'model',
                'is_required' => false,
                'sort_order' => 2,
            ],
        ];

        foreach ($globalQuestions as $question) {
            GlobalQuestion::updateOrCreate(
                ['admin_id' => $adminId, 'field_name' => $question['field_name']],
                array_merge($question, ['admin_id' => $adminId, 'is_active' => true])
            );
        }

        // Create Question Templates (Hindi, English, Hinglish)
        $templates = [
            'category' => [
                'hi' => [
                    'question_text' => 'Aapko kaunsa product chahiye? Cabinet handles, Door handles, Knobs, ya kuch aur?',
                    'confirmation_text' => 'Maine {value} note kar liya.',
                    'error_text' => 'Yeh product hamare paas available nahi hai.',
                ],
                'en' => [
                    'question_text' => 'What product category are you looking for? Cabinet handles, Door handles, Knobs, or something else?',
                    'confirmation_text' => 'Noted: {value}',
                    'error_text' => 'This product is not available.',
                ],
            ],
            'model' => [
                'hi' => [
                    'question_text' => 'Kaunsa model number chahiye aapko?',
                    'confirmation_text' => 'Model {value} note kar liya.',
                    'options_text' => 'Available models: {options}',
                ],
                'en' => [
                    'question_text' => 'Which model number would you like?',
                    'confirmation_text' => 'Model {value} noted.',
                    'options_text' => 'Available models: {options}',
                ],
            ],
            'size' => [
                'hi' => [
                    'question_text' => 'Size kya chahiye? 4inch, 6inch, 8inch?',
                    'confirmation_text' => 'Size {value} note kar liya.',
                ],
                'en' => [
                    'question_text' => 'What size do you need?',
                    'confirmation_text' => 'Size {value} noted.',
                ],
            ],
            'finish' => [
                'hi' => [
                    'question_text' => 'Finish/Color kaunsa chahiye? Gold, Black, Chrome, Antique?',
                    'confirmation_text' => '{value} finish note kar liya.',
                ],
                'en' => [
                    'question_text' => 'What finish/color would you prefer?',
                    'confirmation_text' => '{value} finish noted.',
                ],
            ],
            'qty' => [
                'hi' => [
                    'question_text' => 'Kitne pieces chahiye?',
                    'confirmation_text' => '{value} pcs note kar liya.',
                    'error_text' => 'Kripya valid quantity batayein.',
                ],
                'en' => [
                    'question_text' => 'How many pieces do you need?',
                    'confirmation_text' => '{value} pieces noted.',
                    'error_text' => 'Please provide a valid quantity.',
                ],
            ],
            'city' => [
                'hi' => [
                    'question_text' => 'Aap kis city se ho?',
                    'confirmation_text' => '{value} se ho aap, dhanyavaad!',
                ],
                'en' => [
                    'question_text' => 'Which city are you from?',
                    'confirmation_text' => 'Thanks! Noted that you are from {value}.',
                ],
            ],
            'purpose_of_purchase' => [
                'hi' => [
                    'question_text' => 'Yeh order Wholesale ke liye hai ya Retail ke liye?',
                    'confirmation_text' => '{value} order note kar liya.',
                ],
                'en' => [
                    'question_text' => 'Is this order for Wholesale or Retail?',
                    'confirmation_text' => '{value} order noted.',
                ],
            ],
        ];

        foreach ($templates as $fieldName => $languages) {
            foreach ($languages as $language => $texts) {
                QuestionTemplate::updateOrCreate(
                    [
                        'admin_id' => $adminId,
                        'field_name' => $fieldName,
                        'language' => $language,
                    ],
                    array_merge($texts, [
                        'admin_id' => $adminId,
                        'field_name' => $fieldName,
                        'language' => $language,
                    ])
                );
            }
        }

        $this->command->info('Questionnaire data seeded successfully!');
    }
}
