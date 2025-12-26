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
        $resources = ['users', 'roles', 'ordenes', 'lotes', 'operador'];
        $actions = ['view', 'create', 'edit', 'delete'];

        $allPermissions = [];
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permName = "{$resource}.{$action}";
                $p = Permission::firstOrCreate(['name' => $permName]);
                $allPermissions[] = $p;
            }
        }

        // Permisos específicos para operador
        Permission::firstOrCreate(['name' => 'operador.dashboard']);
        Permission::firstOrCreate(['name' => 'operador.registrar']);
        Permission::firstOrCreate(['name' => 'procesos.registrar']);

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
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

        // Operador: limited to viewing dashboard and registering progress
        $operadorRole = Role::where('name', 'operador')->first();
        if ($operadorRole) {
            $operadorPerms = Permission::whereIn('name', [
                'operador.dashboard',
                'operador.registrar',
                'procesos.registrar',
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
