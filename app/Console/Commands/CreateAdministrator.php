<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdministrator extends Command
{
    protected $signature = 'cdtrack:admin {email} {--name=}';

    protected $description = 'Create an administrator with an interactively entered password';

    public function handle(): int
    {
        $data = ['email' => $this->argument('email'), 'name' => $this->option('name') ?: $this->ask('Full name'), 'password' => $this->secret('Password (at least 12 characters)')];
        $validator = Validator::make($data, ['email' => 'required|email|unique:users', 'name' => 'required|string|max:255', 'password' => ['required', Password::min(12)]]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }
        $user = new User($data);
        $user->role = 'admin';
        $user->save();
        $this->info('Administrator created.');

        return self::SUCCESS;
    }
}
