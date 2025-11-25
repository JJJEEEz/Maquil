<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndUsersSeeder extends Seeder
{
    public function run()
    {
        $roles = ['admin', 'supervisor', 'operador'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@maquil.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles('admin');

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@maquil.test'],
            [
                'name' => 'Supervisor Maquil',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ]
        );
        $supervisor->syncRoles('supervisor');

        $operador = User::firstOrCreate(
            ['email' => 'operador@maquil.test'],
            [
                'name' => 'Operador Maquil',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ]
        );
        $operador->syncRoles('operador');
    }
}
