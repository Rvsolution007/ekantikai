<?php

namespace App\Console\Commands;

use App\Models\SuperAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password {email} {password}';
    protected $description = 'Reset user password';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = SuperAdmin::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password updated successfully for {$email}");
        $this->info("New password hash: " . $user->password);

        return 0;
    }
}
