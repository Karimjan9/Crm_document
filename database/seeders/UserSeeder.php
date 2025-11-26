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
        // 🔹 Rollar ro‘yxati
        $roles = [
            'super_admin',
            'admin_manager',
            'admin_filial',
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

        // 🔹 Userlar ro‘yxati
        $users = [
            [
                'name' => 'Super Admin',
                'login' => 'superadmin',
                'phone' => '1234567899',
                'password' => Hash::make('super123'),
                'role' => 'super_admin',
                'filial_id' => 1,
            ],
            [
                'name' => 'Admin Manager',
                'login' => 'adminmanager',
                'phone' => '1234567898',
                'password' => Hash::make('manager123'),
                'role' => 'admin_manager',
                'filial_id' => 1,
            ],
            [
                'name' => 'Admin Filial',
                'login' => 'adminfilial',
                'phone' => '1234567897',
                'password' => Hash::make('filial123'),
                'role' => 'admin_filial',
                'filial_id' => 1,

            ],
            [
                'name' => 'Employee',
                'login' => 'employee',
                'phone' => '1234567891',
                'password' => Hash::make('employee123'),
                'role' => 'employee',
                'filial_id' => 1,
            ],
            [
                'name' => 'User',
                'login' => 'user',
                'phone' => '1234567892',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'filial_id' => 1,
            ],
            [
                'name' => 'Courier',
                'login' => 'courier',
                'phone' => '1234567893',
                'password' => Hash::make('courier123'),
                'role' => 'courier',
                
            ],
        ];

        // 🔹 Har bir userni yaratish va rolini biriktirish
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
