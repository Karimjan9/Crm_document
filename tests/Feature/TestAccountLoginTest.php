<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TestAccountLoginTest extends TestCase
{
    use RefreshDatabase;

    public static function applicationRoleHomes(): array
    {
        return [
            ['super_admin', 'superadmin.index'],
            ['admin_manager', 'admin.index'],
            ['admin_filial', 'admin_filial.index'],
            ['employee', 'employee.document.index'],
            ['courier', 'courier.documents.index'],
            ['partner_admin', 'partner.dashboard'],
            ['partner_operator', 'partner.dashboard'],
            ['user', 'account.home'],
        ];
    }

    #[DataProvider('applicationRoleHomes')]
    public function test_login_and_guest_pages_redirect_to_the_authorized_role_home(string $role, string $homeRoute): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create([
            'login' => 'role-home-'.$role,
            'password' => Hash::make('test-password'),
        ]);
        $user->assignRole($role);

        $this->post(route('login_post'), [
            'login' => $user->login,
            'password' => 'test-password',
        ])->assertRedirect(route($homeRoute));

        $this->assertAuthenticatedAs($user);
        $this->get(route('login'))->assertRedirect(route($homeRoute));
    }

    public function test_user_role_can_log_in_to_its_limited_account_home(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('user', 'web');

        $user = User::factory()->create([
            'login' => 'test-user-role',
            'password' => Hash::make('test-password'),
        ]);
        $user->assignRole('user');

        $this->post(route('login_post'), [
            'login' => 'test-user-role',
            'password' => 'test-password',
        ])->assertRedirect(route('account.home'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('account.home'))->assertOk()->assertSee('Umumiy foydalanuvchi kabineti');
    }

    public function test_manager_user_forms_and_navigation_use_authorized_manager_routes(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('admin_manager', 'web');
        Role::findOrCreate('employee', 'web');
        Role::findOrCreate('courier', 'web');
        Role::findOrCreate('admin_filial', 'web');
        $manager = User::factory()->create();
        $manager->assignRole('admin_manager');
        $employee = User::factory()->create();
        $employee->assignRole('employee');

        $this->actingAs($manager)->get(route('admin.index'))
            ->assertOk()
            ->assertSee(route('admin.create'), false)
            ->assertSee(route('admin.edit', $employee), false)
            ->assertSee(route('admin.document.index'), false)
            ->assertDontSee(route('superadmin.index'), false);
        $this->get(route('admin.create'))
            ->assertOk()->assertSee('action="'.route('admin.store').'"', false);
        $this->get(route('admin.edit', $employee))
            ->assertOk()->assertSee('action="'.route('admin.update', $employee).'"', false);
    }
}
