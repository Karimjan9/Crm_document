<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 🔹 Rollarni xavfsiz yaratish (agar mavjud bo‘lsa - qayta yaratmaydi)
        $roles = [
            'admin',
            'employee',
            'user',
            'courier',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // 🔹 Userlar yaratish
        $users = [
            [
                'name' => 'Admin',
                'login' => 'admin',
                'phone' => '1234567890',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ],
            [
                'name' => 'Employee',
                'login' => 'employee',
                'phone' => '1234567891',
                'password' => Hash::make('employee123'),
                'role' => 'employee',
            ],
            [
                'name' => 'User',
                'login' => 'user',
                'phone' => '1234567892',
                'password' => Hash::make('user123'),
                'role' => 'user',
            ],
            [
                'name' => 'Courier',
                'login' => 'courier',
                'phone' => '1234567893',
                'password' => Hash::make('courier123'),
                'role' => 'courier',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['login' => $data['login']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => $data['password'],
                ]
            );
            $user->assignRole($data['role']);
        }
    }
}
