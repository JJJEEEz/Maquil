<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('id','desc')->paginate(15);
        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Roles/Form', [
            'role' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        Role::create(['name' => $data['name']]);

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {
        return Inertia::render('Admin/Roles/Form', [
            'role' => $role,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->name = $data['name'];
        $role->save();

        return redirect()->route('admin.roles.index');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('admin.roles.index');
    }
}
