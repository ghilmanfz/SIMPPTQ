<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::with('permissions')->withCount('users')->orderByDesc('is_system')->orderBy('label')->get();
        $permissions = Permission::orderBy('id')->get()->groupBy('group');

        return view('roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Role::create([
            'name' => Str::slug($data['label'], '_'),
            'label' => $data['label'],
            'description' => $data['description'] ?? null,
            'is_system' => false,
        ]);

        return back()->with('success', 'Role baru berhasil dibuat.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role->update($data);

        return back()->with('success', 'Role berhasil diperbarui.');
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        // Super admin selalu memiliki seluruh akses — tidak perlu diubah manual.
        if ($role->name === 'superadmin') {
            return back()->with('error', 'Permission Super Admin tidak dapat diubah (akses penuh otomatis).');
        }

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return back()->with('success', "Hak akses role {$role->label} berhasil diperbarui.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('error', 'Role bawaan sistem tidak dapat dihapus.');
        }
        if ($role->users()->exists()) {
            return back()->with('error', 'Role masih dipakai oleh user. Pindahkan user terlebih dahulu.');
        }

        $role->delete();

        return back()->with('success', 'Role berhasil dihapus.');
    }
}
