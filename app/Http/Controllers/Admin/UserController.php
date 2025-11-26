<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('id','desc')->paginate(15);
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

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'authPermissions' => $authPermissions,
        ]);
    }

    public function create()
    {
        $roles = Role::pluck('name');
        $permissions = Permission::all();
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

        return Inertia::render('Admin/Users/Form', [
            'user' => null,
            'roles' => $roles,
            'permissions' => $permissions,
            'userPermissions' => [],
            'authPermissions' => $authPermissions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'roles' => 'array',
            'permissions' => 'array',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (!empty($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::pluck('name');
        $permissions = Permission::all();
        $userPermissions = $user->getPermissionNames()->toArray();
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

        return Inertia::render('Admin/Users/Form', [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
            'userPermissions' => $userPermissions,
            'authPermissions' => $authPermissions,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8',
            'roles' => 'array',
            'permissions' => 'array',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return redirect()->route('admin.users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index');
    }
}
