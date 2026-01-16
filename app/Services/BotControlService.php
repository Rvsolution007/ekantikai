<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\WhatsappChat;
use Illuminate\Support\Facades\Log;

class BotControlService
{
    /**
     * Parse bot control command from message
     * Format: "{phone} stop" or "{phone} start"
     */
    public function parseCommand(string $message): ?array
    {
        $message = strtolower(trim($message));

        // Pattern: phone number + command
        if (preg_match('/^(\+?\d{10,15})\s*(stop|start)$/i', $message, $matches)) {
            return [
                'phone' => $this->normalizePhone($matches[1]),
                'command' => strtolower($matches[2]),
            ];
        }

        // Pattern: command + phone number
        if (preg_match('/^(stop|start)\s*(\+?\d{10,15})$/i', $message, $matches)) {
            return [
                'phone' => $this->normalizePhone($matches[2]),
                'command' => strtolower($matches[1]),
            ];
        }

        return null;
    }

    /**
     * Check if sender is the bot control number for this admin
     */
    public function isControlNumber(Admin $admin, string $senderPhone): bool
    {
        $controlNumber = $admin->bot_control_number;
        if (!$controlNumber) {
            return false;
        }

        return $this->normalizePhone($senderPhone) === $this->normalizePhone($controlNumber);
    }

    /**
     * Execute bot control command
     */
    public function executeCommand(Admin $admin, string $targetPhone, string $command): array
    {
        $normalizedPhone = $this->normalizePhone($targetPhone);

        // Find customer
        $customer = Customer::where('admin_id', $admin->id)
            ->where('phone', $normalizedPhone)
            ->first();

        if (!$customer) {
            return [
                'success' => false,
                'message' => "Customer with phone {$targetPhone} not found.",
            ];
        }

        try {
            if ($command === 'stop') {
                $customer->stopBot('admin_command');
                $this->logCommand($admin, $customer, 'stop');

                return [
                    'success' => true,
                    'message' => "Bot stopped for {$customer->name} ({$targetPhone}).",
                    'customer' => $customer,
                ];
            } elseif ($command === 'start') {
                $customer->startBot();
                $this->logCommand($admin, $customer, 'start');

                return [
                    'success' => true,
                    'message' => "Bot started for {$customer->name} ({$targetPhone}).",
                    'customer' => $customer,
                ];
            }

            return [
                'success' => false,
                'message' => "Unknown command: {$command}",
            ];
        } catch (\Exception $e) {
            Log::error('Bot control command failed', [
                'admin_id' => $admin->id,
                'phone' => $targetPhone,
                'command' => $command,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => "Failed to execute command: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Log bot control command to chat
     */
    protected function logCommand(Admin $admin, Customer $customer, string $command): void
    {
        WhatsappChat::create([
            'admin_id' => $admin->id,
            'customer_id' => $customer->id,
            'number' => $customer->phone,
            'role' => WhatsappChat::ROLE_SYSTEM,
            'content' => "Bot {$command}ed by admin via WhatsApp command.",
            'metadata' => [
                'type' => 'bot_control',
                'command' => $command,
                'executed_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Normalize phone number for comparison
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-digits
        $phone = preg_replace('/\D/', '', $phone);

        // Remove leading zeros or country code prefix
        if (strlen($phone) > 10 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }

        return $phone;
    }

    /**
     * Handle incoming control message
     */
    public function handleControlMessage(Admin $admin, string $senderPhone, string $message): ?array
    {
        // Check if sender is authorized control number
        if (!$this->isControlNumber($admin, $senderPhone)) {
            return null; // Not a control message
        }

        // Parse command
        $parsed = $this->parseCommand($message);
        if (!$parsed) {
            return [
                'success' => false,
                'message' => 'Invalid command format. Use: {phone} stop or {phone} start',
            ];
        }

        // Execute command
        return $this->executeCommand($admin, $parsed['phone'], $parsed['command']);
    }
}
