<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Followup;
use App\Models\FollowupTemplate;
use App\Models\Lead;
use App\Models\WhatsappChat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FollowupService
{
    /**
     * Process due followups for an admin
     */
    public function processDueFollowups(Admin $admin): int
    {
        $processed = 0;

        // Get followup templates for this admin
        $templates = FollowupTemplate::where('admin_id', $admin->id)
            ->active()
            ->ordered()
            ->get();

        if ($templates->isEmpty()) {
            return 0;
        }

        // Get leads that need followup
        $leads = Lead::where('admin_id', $admin->id)
            ->where('status', 'open')
            ->where('bot_active', true)
            ->with('customer')
            ->get();

        foreach ($leads as $lead) {
            if ($this->shouldSendFollowup($admin, $lead)) {
                $this->sendFollowup($admin, $lead, $templates->first());
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Check if lead needs a followup
     */
    public function shouldSendFollowup(Admin $admin, Lead $lead): bool
    {
        $customer = $lead->customer;
        if (!$customer) {
            return false;
        }

        // Get last activity
        $lastActivity = $customer->last_activity_at;
        if (!$lastActivity) {
            return false;
        }

        $delayMinutes = $admin->followup_delay_minutes ?? 60;
        $minutesSinceActivity = $lastActivity->diffInMinutes(now());

        return $minutesSinceActivity >= $delayMinutes;
    }

    /**
     * Send followup message to a lead
     */
    public function sendFollowup(Admin $admin, Lead $lead, FollowupTemplate $template): bool
    {
        try {
            $customer = $lead->customer;
            if (!$customer) {
                return false;
            }

            // Render the template with lead data
            $message = $template->render($lead);

            // Send via WhatsApp
            $sent = $this->sendWhatsAppMessage($admin, $customer->phone, $message);

            if ($sent) {
                // Log the message
                WhatsappChat::create([
                    'admin_id' => $admin->id,
                    'customer_id' => $customer->id,
                    'number' => $customer->phone,
                    'role' => WhatsappChat::ROLE_ASSISTANT,
                    'content' => $message,
                    'metadata' => [
                        'type' => 'followup',
                        'template_id' => $template->id,
                        'template_name' => $template->name,
                    ],
                ]);

                // Update last activity
                $customer->updateLastActivity();

                // Create followup record
                Followup::create([
                    'lead_id' => $lead->id,
                    'status' => Followup::STATUS_COMPLETED,
                    'ai_message' => $message,
                    'last_activity_at' => now(),
                ]);

                Log::info('Followup sent', [
                    'admin_id' => $admin->id,
                    'lead_id' => $lead->id,
                    'template_id' => $template->id,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Followup failed', [
                'admin_id' => $admin->id,
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send WhatsApp message via Evolution API
     */
    protected function sendWhatsAppMessage(Admin $admin, string $phone, string $message): bool
    {
        try {
            $apiUrl = $admin->whatsapp_api_url;
            $apiKey = $admin->whatsapp_api_key;
            $instance = $admin->whatsapp_instance;

            if (!$apiUrl || !$apiKey || !$instance) {
                Log::warning('WhatsApp not configured for admin', ['admin_id' => $admin->id]);
                return false;
            }

            $response = Http::withHeaders([
                'apikey' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$apiUrl}/message/sendText/{$instance}", [
                        'number' => $this->formatPhoneForWhatsApp($phone),
                        'text' => $message,
                    ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Format phone for WhatsApp API
     */
    protected function formatPhoneForWhatsApp(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return $phone;
    }

    /**
     * Get next followup template to use
     */
    public function getNextTemplate(Admin $admin, Lead $lead): ?FollowupTemplate
    {
        // Count how many followups have been sent
        $sentCount = Followup::where('lead_id', $lead->id)
            ->where('status', Followup::STATUS_COMPLETED)
            ->count();

        // Get template by order
        return FollowupTemplate::where('admin_id', $admin->id)
            ->active()
            ->ordered()
            ->skip($sentCount)
            ->first();
    }

    /**
     * Schedule next followup for a lead
     */
    public function scheduleNextFollowup(Admin $admin, Lead $lead): ?Followup
    {
        $template = $this->getNextTemplate($admin, $lead);
        if (!$template) {
            return null;
        }

        $delayMinutes = $template->delay_minutes ?? $admin->followup_delay_minutes ?? 60;

        return Followup::create([
            'lead_id' => $lead->id,
            'status' => Followup::STATUS_PENDING,
            'next_followup_at' => now()->addMinutes($delayMinutes),
        ]);
    }
}
