<?php

namespace Database\Seeders;

use App\Models\FilialModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (FilialModel::query()->doesntExist()) {
            $this->call(FilialSeeder::class);
        }

        $defaultFilialId = FilialModel::query()->value('id');

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

        $users = [
            [
                'name' => 'Super Admin',
                'login' => 'superadmin',
                'phone' => '1234567899',
                'password' => Hash::make('super123'),
                'role' => 'super_admin',
                'filial_id' => $defaultFilialId,
            ],
            [
                'name' => 'Admin Manager',
                'login' => 'adminmanager',
                'phone' => '1234567898',
                'password' => Hash::make('manager123'),
                'role' => 'admin_manager',
                'filial_id' => $defaultFilialId,
            ],
            [
                'name' => 'Admin Filial',
                'login' => 'adminfilial',
                'phone' => '1234567897',
                'password' => Hash::make('filial123'),
                'role' => 'admin_filial',
                'filial_id' => $defaultFilialId,
            ],
            [
                'name' => 'Employee',
                'login' => 'employee',
                'phone' => '1234567891',
                'password' => Hash::make('employee123'),
                'role' => 'employee',
                'filial_id' => $defaultFilialId,
            ],
            [
                'name' => 'User',
                'login' => 'user',
                'phone' => '1234567892',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'filial_id' => $defaultFilialId,
            ],
            [
                'name' => 'Courier',
                'login' => 'courier',
                'phone' => '1234567893',
                'password' => Hash::make('courier123'),
                'role' => 'courier',
                'filial_id' => null,
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];

            $user = User::updateOrCreate(
                ['login' => $data['login']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'filial_id' => $data['filial_id'],
                    'password' => $data['password'],
                    'settings' => [
                        'seeded' => true,
                        'type' => 'default',
                    ],
                ]
            );

            $user->syncRoles([$role]);
        }
    }
}
