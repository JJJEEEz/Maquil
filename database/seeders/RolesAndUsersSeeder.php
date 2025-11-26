<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndUsersSeeder extends Seeder
{
    public function run()
    {
        $roles = ['admin', 'supervisor', 'operador'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        // Define resources and actions
        $resources = ['users', 'roles', 'ordenes', 'lotes'];
        $actions = ['view', 'create', 'edit', 'delete'];

        $allPermissions = [];
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permName = "{$resource}.{$action}";
                $p = Permission::firstOrCreate(['name' => $permName]);
                $allPermissions[] = $p;
            }
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions($allPermissions);
        }

        // Supervisor: manage ordenes and lotes
        $supervisorRole = Role::where('name', 'supervisor')->first();
        if ($supervisorRole) {
            $supervisorPerms = Permission::whereIn('name', [
                'ordenes.view','ordenes.create','ordenes.edit','ordenes.delete',
                'lotes.view','lotes.create','lotes.edit','lotes.delete',
            ])->get();
            $supervisorRole->syncPermissions($supervisorPerms);
        }

        // Operador: limited to lotes view/create/edit
        $operadorRole = Role::where('name', 'operador')->first();
        if ($operadorRole) {
            $operadorPerms = Permission::whereIn('name', [
                'lotes.view','lotes.create','lotes.edit',
            ])->get();
            $operadorRole->syncPermissions($operadorPerms);
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
