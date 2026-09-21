<?php

namespace Database\Seeders;

use App\Models\FilialModel;
use App\Models\Partner;
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

        $demoPartner = Partner::updateOrCreate(
            ['code' => 'DEMO-PARTNER'],
            [
                'company_name' => 'Demo Partner',
                'type' => 'other',
                'contact_name' => 'Demo Partner Admin',
                'discount_percent' => 0,
                'credit_limit' => 0,
                'payment_terms_days' => 30,
                'currency' => 'UZS',
                'status' => 'active',
                'brand_name' => 'Demo Partner',
            ]
        );

        $demoPartner->filials()->syncWithoutDetaching([
            $defaultFilialId => ['is_active' => true],
        ]);

        $roles = [
            'super_admin',
            'admin_manager',
            'admin_filial',
            'employee',
            'user',
            'courier',
            'partner_admin',
            'partner_operator',
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
            [
                'name' => 'Demo Partner Admin',
                'login' => 'partneradmin',
                'phone' => '1234567894',
                'password' => Hash::make('partneradmin123'),
                'role' => 'partner_admin',
                'filial_id' => null,
                'partner_id' => $demoPartner->id,
            ],
            [
                'name' => 'Demo Partner Operator',
                'login' => 'partneroperator',
                'phone' => '1234567895',
                'password' => Hash::make('partneroperator123'),
                'role' => 'partner_operator',
                'filial_id' => null,
                'partner_id' => $demoPartner->id,
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
                    'partner_id' => $data['partner_id'] ?? null,
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
