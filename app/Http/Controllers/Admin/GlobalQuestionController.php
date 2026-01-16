<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalQuestion;
use Illuminate\Http\Request;

class GlobalQuestionController extends Controller
{
    /**
     * Display global questions
     */
    public function index()
    {
        $adminId = $this->getAdminId();

        $questions = GlobalQuestion::where('admin_id', $adminId)
            ->orderBy('sort_order')
            ->get();

        return view('admin.workflow.global.index', [
            'questions' => $questions,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $adminId = $this->getAdminId();

        $fields = \App\Models\QuestionnaireField::where('admin_id', $adminId)
            ->active()
            ->ordered()
            ->pluck('display_name', 'field_name');

        return view('admin.workflow.global.create', [
            'fields' => $fields,
        ]);
    }

    /**
     * Store new global question
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:50|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:100',
            'question_type' => 'nullable|in:text,select',
            'options' => 'nullable|string',
            'add_question' => 'nullable|string|max:500',
        ]);

        $adminId = $this->getAdminId();

        // Check if field name already exists
        if (GlobalQuestion::where('admin_id', $adminId)->where('question_name', $validated['field_name'])->exists()) {
            return back()->withErrors(['field_name' => 'This field name already exists'])->withInput();
        }

        // Get max sort order
        $maxSort = GlobalQuestion::where('admin_id', $adminId)->max('sort_order') ?? 0;

        // Parse options if provided
        $options = null;
        if (!empty($validated['options'])) {
            $options = array_map('trim', explode(',', $validated['options']));
        }

        GlobalQuestion::create([
            'admin_id' => $adminId,
            'question_name' => $validated['field_name'],
            'field_name' => $validated['field_name'],
            'display_name' => $validated['display_name'],
            'question_type' => $validated['question_type'] ?? 'text',
            'field_type' => $validated['question_type'] ?? 'text',
            'options' => $options,
            'add_question' => $validated['add_question'] ?? null,
            'trigger_position' => 'first',
            'sort_order' => $maxSort + 1,
            'is_active' => true,
        ]);

        return redirect()->route('admin.workflow.global.index')
            ->with('success', 'Global question added successfully');
    }

    /**
     * Show edit form
     */
    public function edit(GlobalQuestion $question)
    {
        $this->authorizeQuestion($question);

        $adminId = $this->getAdminId();

        $fields = \App\Models\QuestionnaireField::where('admin_id', $adminId)
            ->active()
            ->ordered()
            ->pluck('display_name', 'field_name');

        return view('admin.workflow.global.edit', [
            'question' => $question,
            'fields' => $fields,
        ]);
    }

    /**
     * Update global question
     */
    public function update(Request $request, GlobalQuestion $question)
    {
        $this->authorizeQuestion($question);

        $validated = $request->validate([
            'field_name' => 'nullable|string|max:50',
            'display_name' => 'required|string|max:100',
            'question_type' => 'nullable|in:text,select',
            'options' => 'nullable|string',
            'add_question' => 'nullable|string|max:500',
        ]);

        // Parse options if provided
        $options = null;
        if (!empty($validated['options'])) {
            $options = array_map('trim', explode(',', $validated['options']));
        }

        $question->update([
            'display_name' => $validated['display_name'],
            'question_type' => $validated['question_type'] ?? $question->question_type,
            'options' => $options,
            'add_question' => $validated['add_question'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.workflow.global.index')
            ->with('success', 'Global question updated successfully');
    }

    /**
     * Delete global question
     */
    public function destroy(GlobalQuestion $question)
    {
        $this->authorizeQuestion($question);

        $question->delete();

        return redirect()->route('admin.workflow.global.index')
            ->with('success', 'Global question deleted successfully');
    }

    protected function getAdminId(): int
    {
        $admin = auth()->guard('admin')->user();
        return $admin->admin_id ?? 1;
    }

    protected function authorizeQuestion(GlobalQuestion $question): void
    {
        if ($question->admin_id !== $this->getAdminId()) {
            abort(403);
        }
    }
}
