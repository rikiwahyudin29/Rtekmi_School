<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        // Get all roles
        $roles = Role::all();

        return Inertia::render('Admin/Users/Roles', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_key' => 'required|string|max:50|unique:roles,role_key',
            'role_name' => 'required|string|max:255',
        ]);

        Role::create($validated);

        return redirect()->back()->with('success', 'Role berhasil ditambahkan.');
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'role_key' => 'required|string|max:50|unique:roles,role_key,' . $role->id,
            'role_name' => 'required|string|max:255',
        ]);

        $role->update($validated);

        return redirect()->back()->with('success', 'Role berhasil diperbarui.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Optional: Protect core roles from deletion
        if (in_array($role->role_key, ['superadmin', 'admin', 'kepsek', 'guru', 'siswa'])) {
            return redirect()->back()->with('error', 'Role sistem tidak dapat dihapus.');
        }

        $role->delete();

        return redirect()->back()->with('success', 'Role berhasil dihapus.');
    }
}
