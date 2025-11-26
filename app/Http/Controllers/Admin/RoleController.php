<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('id','desc')->paginate(15);
        $authPermissions = [];
        if (auth()->check()) {
            $userId = auth()->user()->getKey();
            $rolePermissions = DB::table('permissions')
                ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_id', $userId)
                ->pluck('permissions.name')
                ->toArray();
            $directPermissions = DB::table('permissions')
                ->join('model_has_permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
                ->where('model_has_permissions.model_id', $userId)
                ->pluck('permissions.name')
                ->toArray();
            $authPermissions = array_values(array_unique(array_merge($rolePermissions, $directPermissions)));
        }

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'authPermissions' => $authPermissions,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/Form', [
                'role' => null,
                'permissions' => Permission::orderBy('name')->get()->toArray(),
                'rolePermissions' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

            $role = Role::create(['name' => $data['name']]);

            // sync permissions if provided
            if ($request->has('permissions') && is_array($request->input('permissions'))) {
                $role->syncPermissions($request->input('permissions'));
            }

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {
        return Inertia::render('Admin/Roles/Form', [
                'role' => $role,
                'permissions' => Permission::orderBy('name')->get()->toArray(),
                'rolePermissions' => $role->permissions->pluck('name')->toArray(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->name = $data['name'];
        $role->save();

            // sync permissions if provided
            if ($request->has('permissions') && is_array($request->input('permissions'))) {
                $role->syncPermissions($request->input('permissions'));
            }

        return redirect()->route('admin.roles.index');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('admin.roles.index');
    }
}
