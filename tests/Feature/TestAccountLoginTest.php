<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TestAccountLoginTest extends TestCase
{
    use RefreshDatabase;

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
}
