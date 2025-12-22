<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProcessesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Tipos de Prendas
            'tipos_prendas.view',
            'tipos_prendas.create',
            'tipos_prendas.edit',
            'tipos_prendas.delete',

            // Procesos
            'procesos.view',
            'procesos.create',
            'procesos.edit',
            'procesos.delete',

            // Registro de Procesos
            'procesos.registrar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->first();
        $operadorRole = Role::where('name', 'operador')->first();
        $supervisorRole = Role::where('name', 'supervisor')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo(Permission::all());
        }

        if ($operadorRole) {
            $operadorRole->givePermissionTo([
                'procesos.registrar',
                'procesos.view',
            ]);
        }

        if ($supervisorRole) {
            $supervisorRole->givePermissionTo([
                'procesos.registrar',
                'procesos.view',
                'tipos_prendas.view',
            ]);
        }
    }
}
