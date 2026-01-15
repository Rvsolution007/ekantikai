<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionTemplate;
use App\Models\QuestionnaireField;
use App\Models\GlobalQuestion;
use Illuminate\Http\Request;

class QuestionTemplateController extends Controller
{
    /**
     * Display question templates
     */
    public function index()
    {
        $adminId = $this->getAdminId();

        // Get all fields (questionnaire + global)
        $questionnaireFields = QuestionnaireField::where('admin_id', $adminId)
            ->active()
            ->get(['field_name', 'display_name']);

        $globalFields = GlobalQuestion::where('admin_id', $adminId)
            ->active()
            ->get(['field_name', 'display_name']);

        $allFields = $questionnaireFields->merge($globalFields);

        // Get existing templates
        $templates = QuestionTemplate::where('admin_id', $adminId)->get();

        // Group by field and language
        $templatesByField = $templates->groupBy('field_name');

        return view('admin.questionnaire.templates.index', [
            'fields' => $allFields,
            'templatesByField' => $templatesByField,
            'languages' => $this->getLanguages(),
        ]);
    }

    /**
     * Show create/edit form for a field
     */
    public function edit(string $fieldName)
    {
        $adminId = $this->getAdminId();

        $templates = QuestionTemplate::where('admin_id', $adminId)
            ->where('field_name', $fieldName)
            ->get()
            ->keyBy('language');

        return view('admin.questionnaire.templates.edit', [
            'fieldName' => $fieldName,
            'templates' => $templates,
            'languages' => $this->getLanguages(),
        ]);
    }

    /**
     * Store/update templates for a field
     */
    public function store(Request $request, string $fieldName)
    {
        $adminId = $this->getAdminId();

        $validated = $request->validate([
            'templates' => 'required|array',
            'templates.*.language' => 'required|string|max:20',
            'templates.*.question_text' => 'required|string',
            'templates.*.confirmation_text' => 'nullable|string',
            'templates.*.error_text' => 'nullable|string',
            'templates.*.options_text' => 'nullable|string',
        ]);

        foreach ($validated['templates'] as $data) {
            QuestionTemplate::updateOrCreate(
                [
                    'admin_id' => $adminId,
                    'field_name' => $fieldName,
                    'language' => $data['language'],
                ],
                [
                    'question_text' => $data['question_text'],
                    'confirmation_text' => $data['confirmation_text'] ?? null,
                    'error_text' => $data['error_text'] ?? null,
                    'options_text' => $data['options_text'] ?? null,
                ]
            );
        }

        return redirect()->route('admin.questionnaire.templates.index')
            ->with('success', 'Templates saved successfully');
    }

    /**
     * Delete template
     */
    public function destroy(QuestionTemplate $template)
    {
        $this->authorizeTemplate($template);

        $template->delete();

        return back()->with('success', 'Template deleted');
    }

    protected function getAdminId(): int
    {
        $admin = auth()->guard('admin')->user();
        return $admin->admin_id ?? 1;
    }

    protected function authorizeTemplate(QuestionTemplate $template): void
    {
        if ($template->admin_id !== $this->getAdminId()) {
            abort(403);
        }
    }

    protected function getLanguages(): array
    {
        return [
            'hi' => 'Hindi (हिन्दी)',
            'en' => 'English',
            'gu' => 'Gujarati (ગુજરાતી)',
            'ta' => 'Tamil (தமிழ்)',
            'hinglish' => 'Hinglish',
        ];
    }
}
