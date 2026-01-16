<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Admin;
use App\Models\FollowupTemplate;
use App\Services\FollowupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ProcessFollowupsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get all admins with followup enabled
        $admins = Admin::whereNotNull('followup_delay_minutes')
            ->where('followup_delay_minutes', '>', 0)
            ->get();

        foreach ($admins as $admin) {
            $this->processAdminFollowups($admin);
        }
    }

    /**
     * Process followups for a specific admin
     */
    protected function processAdminFollowups(Admin $admin): void
    {
        $delayMinutes = $admin->followup_delay_minutes ?? 60;
        $cutoffTime = Carbon::now()->subMinutes($delayMinutes);

        // Get customers who:
        // 1. Belong to this admin
        // 2. Haven't responded in X minutes
        // 3. Bot is still active for them
        // 4. Haven't completed all questions
        $customers = Customer::where('admin_id', $admin->id)
            ->where('bot_stopped_by_user', false)
            ->where('last_message_at', '<', $cutoffTime)
            ->where('followup_sent_at', '<', Carbon::now()->subMinutes($delayMinutes))
            ->orWhereNull('followup_sent_at')
            ->get();

        foreach ($customers as $customer) {
            $this->sendFollowup($admin, $customer);
        }
    }

    /**
     * Send followup message to customer
     */
    protected function sendFollowup(Admin $admin, Customer $customer): void
    {
        try {
            // Get next followup template
            $template = FollowupTemplate::where('admin_id', $admin->id)
                ->where('is_active', true)
                ->orderBy('order')
                ->first();

            if (!$template) {
                return; // No active templates
            }

            // Use FollowupService to send
            $followupService = app(FollowupService::class);
            $followupService->sendFollowup($customer, $template);

            // Update customer follow-up timestamp
            $customer->update(['followup_sent_at' => Carbon::now()]);

        } catch (\Exception $e) {
            \Log::error('Followup failed: ' . $e->getMessage(), [
                'customer_id' => $customer->id,
                'admin_id' => $admin->id
            ]);
        }
    }
}
