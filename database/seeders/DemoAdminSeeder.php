<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') !== 'local') {
            return;
        }

        $name = trim((string) config('app.demo_admin.name'));
        $email = strtolower(trim((string) config('app.demo_admin.email')));
        $password = (string) config('app.demo_admin.password');

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            throw new RuntimeException('Demo admin credentials must include a name, valid email, and password of at least 12 characters.');
        }

        $admin = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'is_admin' => true,
            ],
        );

        if (! $admin->is_admin) {
            throw new RuntimeException("The configured demo admin email is already used by a non-admin account: {$email}");
        }
    }
}
