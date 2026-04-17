<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteUser extends Command
{
    protected $signature = 'user:promote {email} {role=super_admin : One of user, admin, super_admin}';

    protected $description = 'Promote (or demote) a user to the given role';

    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->argument('role');

        if (!in_array($role, ['user', 'admin', 'super_admin'], true)) {
            $this->error('Role must be one of: user, admin, super_admin');
            return self::INVALID;
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        $user->update(['role' => $role]);
        $this->info("Set {$email} to role '{$role}'.");

        return self::SUCCESS;
    }
}
