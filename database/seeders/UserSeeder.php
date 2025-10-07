<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $role = Role::create(['name' => 'admin']);

        $role = Role::create(['name' => 'epmployee']);

        $role = Role::create(['name' => 'user']);

        $role = Role::create(['name' => 'courier']);

        $user=User::create([
            'name'=>'Admin',
            'login'=>'admin',
            'phone'=>'1234567890',
            'password'=>bcrypt('admin123')
        ]);
        $user->assignRole('admin');
        $user=User::create([
            'name'=>'Employee',
            'login'=>'employee',
            'phone'=>'1234567891',
            'password'=>bcrypt('employee123')
        ]);
        $user->assignRole('epmployee');
        $user=User::create([
            'name'=>'User',
            'login'=>'user',
            'phone'=>'1234567892',
            'password'=>bcrypt('user123')
        ]);
        $user->assignRole('user');
        $user=User::create([
            'name'=>'Courier',
            'login'=>'courier',
            'phone'=>'1234567893',
            'password'=>bcrypt('courier123')
        ]);
        $user->assignRole('courier');
    }
}
