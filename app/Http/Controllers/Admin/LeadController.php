<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Display leads listing
     */
    public function index(Request $request)
    {
        $adminId = auth('admin')->id();

        $query = Lead::with(['whatsappUser', 'customer', 'assignedAdmin'])
            ->where('admin_id', $adminId);

        // Filter by stage
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by quality
        if ($request->filled('quality')) {
            $query->where('lead_quality', $request->quality);
        }

        // Filter by assigned admin
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Search by user name or number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('whatsappUser', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('number', 'like', "%{$search}%");
                })->orWhereHas('customer', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $leads = $query->latest()->paginate(20);
        $admins = SuperAdmin::where('is_active', true)->get();

        // Stats (filtered by admin_id)
        $stats = [
            'total' => Lead::where('admin_id', $adminId)->count(),
            'new' => Lead::where('admin_id', $adminId)->where('stage', 'New Lead')->count(),
            'qualified' => Lead::where('admin_id', $adminId)->where('stage', 'Qualified')->count(),
            'confirmed' => Lead::where('admin_id', $adminId)->where('stage', 'Confirm')->count(),
            'lost' => Lead::where('admin_id', $adminId)->where('stage', 'Lose')->count(),
        ];

        return view('admin.leads.index', compact('leads', 'admins', 'stats'));
    }

    /**
     * Show lead details
     */
    public function show(Lead $lead)
    {
        $adminId = auth('admin')->id();

        // Load relationships - exclude leadProducts as table may not exist
        $lead->load('whatsappUser', 'customer', 'products', 'followups', 'assignedAdmin');

        // Try to load leadProducts if table exists
        try {
            if (\Schema::hasTable('lead_products')) {
                $lead->load('leadProducts');
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet
        }

        // Get chat history - try customer first, then whatsappUser
        $chats = collect();
        if ($lead->customer) {
            // Get chats from chat_messages table via customer
            $chats = \DB::table('chat_messages')
                ->where('customer_id', $lead->customer_id)
                ->orderBy('created_at', 'asc')
                ->get();
        } elseif ($lead->whatsappUser) {
            $chats = $lead->whatsappUser->chats()->orderBy('created_at', 'asc')->get();
        }

        // Get Product fields from QuestionnaireField (Product Questions) for table columns
        // These are the fields defined in Workflow -> Product Questions
        $productFields = \App\Models\QuestionnaireField::where('admin_id', $adminId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($field) {
                return (object) [
                    'field_name' => $field->field_name,
                    'display_name' => $field->display_name,
                ];
            });

        // DEBUG: Log QuestionnaireField count
        \Log::info('QuestionnaireFields for Product Quotation', [
            'admin_id' => $adminId,
            'fields_count' => $productFields->count(),
            'field_names' => $productFields->pluck('display_name')->toArray(),
        ]);

        // Fallback to CatalogueField if no QuestionnaireField defined
        if ($productFields->isEmpty()) {
            $catalogueFields = \App\Models\CatalogueField::where('admin_id', $adminId)
                ->orderBy('sort_order')
                ->get();

            $productFields = $catalogueFields->map(function ($field) {
                return (object) [
                    'field_name' => $field->field_key,
                    'display_name' => $field->field_name,
                ];
            });
        }

        // NOTE: Removed global_questions filter - all product fields should show

        // Get client data for this customer (if exists)
        $client = null;
        if ($lead->customer) {
            $client = \App\Models\Client::where('customer_id', $lead->customer_id)->first();
        }

        return view('admin.leads.show', compact('lead', 'chats', 'productFields', 'client'));
    }

    /**
     * Update lead stage
     */
    public function updateStage(Request $request, Lead $lead)
    {
        $request->validate([
            'stage' => 'required|in:New Lead,Qualified,Confirm,Lose',
        ]);

        $lead->updateStage($request->stage);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Lead stage updated.']);
        }

        return back()->with('success', 'Lead stage updated successfully.');
    }

    /**
     * Assign lead to admin
     */
    public function assign(Request $request, Lead $lead)
    {
        $request->validate([
            'admin_id' => 'nullable|exists:super_admins,id',
        ]);

        $lead->update(['assigned_to' => $request->admin_id]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Lead assigned.']);
        }

        return back()->with('success', 'Lead assigned successfully.');
    }

    /**
     * Export leads to CSV
     */
    public function export(Request $request)
    {
        $leads = Lead::with('whatsappUser', 'products')->get();

        $filename = 'leads_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Phone Number',
                'Name',
                'City',
                'Stage',
                'Quality',
                'Purpose',
                'Products',
                'Created At',
                'Confirmed At'
            ]);

            foreach ($leads as $lead) {
                $products = $lead->products->map(function ($p) {
                    return "{$p->product} - {$p->model}";
                })->implode('; ');

                fputcsv($file, [
                    $lead->id,
                    $lead->whatsappUser->number ?? '',
                    $lead->whatsappUser->name ?? '',
                    $lead->whatsappUser->city ?? '',
                    $lead->stage,
                    $lead->lead_quality,
                    $lead->purpose_of_purchase ?? '',
                    $products,
                    $lead->created_at->format('Y-m-d H:i'),
                    $lead->confirmed_at?->format('Y-m-d H:i') ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Update lead notes
     */
    public function update(Request $request, Lead $lead)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $lead->update(['notes' => $request->notes]);

        return back()->with('success', 'Notes updated successfully.');
    }

    /**
     * Delete a product from lead with passcode verification
     */
    public function deleteProduct(Request $request, Lead $lead, $productIndex)
    {
        $admin = auth('admin')->user();

        // Verify passcode
        $passcode = $request->input('passcode');
        $storedPasscode = $admin->delete_passcode;

        if (empty($storedPasscode)) {
            return response()->json([
                'success' => false,
                'message' => 'Delete passcode not set. Please configure it in Settings.'
            ], 400);
        }

        if ($passcode !== $storedPasscode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid passcode. Please try again.'
            ], 403);
        }

        // Check if lead belongs to admin
        if ($lead->admin_id !== $admin->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $source = $request->input('source', 'lead_product');
        $collectedData = $lead->collected_data ?? [];

        // Delete based on source
        if ($source === 'lead_product') {
            // productIndex is actually the LeadProduct ID
            $leadProduct = \App\Models\LeadProduct::where('lead_id', $lead->id)
                ->where('id', $productIndex)
                ->first();

            if ($leadProduct) {
                $leadProduct->delete();
                return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
            }
        } elseif ($source === 'confirmation') {
            // Delete from product_confirmations
            $confirmations = $lead->product_confirmations ?? [];
            $index = (int) $productIndex;
            if (isset($confirmations[$index])) {
                array_splice($confirmations, $index, 1);
                $lead->product_confirmations = $confirmations;
                $lead->save();
                return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
            }
        } elseif ($source === 'workflow') {
            // Delete workflow_questions from collected_data
            if (isset($collectedData['workflow_questions'])) {
                unset($collectedData['workflow_questions']);
                $lead->collected_data = $collectedData;
                $lead->save();
                return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
            }
        } elseif ($source === 'collected') {
            // Delete from collected_data.products array
            if (isset($collectedData['products']) && is_array($collectedData['products'])) {
                $index = (int) $productIndex;
                if (isset($collectedData['products'][$index])) {
                    array_splice($collectedData['products'], $index, 1);
                    $lead->collected_data = $collectedData;
                    $lead->save();
                    return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
                }
            }
        }

        return response()->json(['success' => false, 'message' => 'Product not found'], 404);
    }

    /**
     * Update product in Product Quotation
     */
    public function updateProduct(Request $request, Lead $lead)
    {
        $request->validate([
            'source' => 'required|string',
            'product_id' => 'required',
            'product_data' => 'required|array',
        ]);

        $source = $request->source;
        $productId = $request->product_id;
        $productData = $request->product_data;
        $collectedData = $lead->collected_data ?? [];

        if ($source === 'lead_products') {
            // Update LeadProduct
            $leadProduct = \App\Models\LeadProduct::where('lead_id', $lead->id)
                ->where('id', $productId)
                ->first();

            if ($leadProduct) {
                // Merge new data with existing
                $existingData = $leadProduct->product_data ?? [];
                $mergedData = array_merge($existingData, $productData);
                $leadProduct->product_data = $mergedData;
                $leadProduct->save();
                return response()->json(['success' => true, 'message' => 'Product updated successfully']);
            }
        } elseif ($source === 'confirmation') {
            // Update product_confirmations
            $confirmations = $lead->product_confirmations ?? [];
            $index = (int) $productId;
            if (isset($confirmations[$index])) {
                // Merge new data with existing
                foreach ($productData as $key => $value) {
                    $confirmations[$index][$key] = $value;
                }
                $lead->product_confirmations = $confirmations;
                $lead->save();
                return response()->json(['success' => true, 'message' => 'Product updated successfully']);
            }
        } elseif ($source === 'workflow') {
            // Update workflow_questions in collected_data
            if (!isset($collectedData['workflow_questions'])) {
                $collectedData['workflow_questions'] = [];
            }
            foreach ($productData as $key => $value) {
                $collectedData['workflow_questions'][$key] = $value;
            }
            $lead->collected_data = $collectedData;
            $lead->save();
            return response()->json(['success' => true, 'message' => 'Product updated successfully']);
        } elseif ($source === 'collected') {
            // Update collected_data.products array
            if (isset($collectedData['products']) && is_array($collectedData['products'])) {
                $index = (int) $productId;
                if (isset($collectedData['products'][$index])) {
                    foreach ($productData as $key => $value) {
                        $collectedData['products'][$index][$key] = $value;
                    }
                    $lead->collected_data = $collectedData;
                    $lead->save();
                    return response()->json(['success' => true, 'message' => 'Product updated successfully']);
                }
            }
        }

        return response()->json(['success' => false, 'message' => 'Product not found'], 404);
    }
}
