<?php

namespace App\Http\Controllers;

use App\Models\Personil;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('role', 'personil')->orderBy('name')->paginate(10);
        $roles = Role::orderBy('label')->get();
        $availablePersonil = Personil::whereNull('user_id')->orderBy('name')->get();

        return view('users.index', compact('users', 'roles', 'availablePersonil'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'role_id' => ['required', 'exists:roles,id'],
            'personil_id' => ['nullable', 'exists:personils,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->linkPersonil($user, $data['personil_id'] ?? null);

        return back()->with('success', 'Akun user berhasil dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'personil_id' => ['nullable', 'exists:personils,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->linkPersonil($user, $data['personil_id'] ?? null);

        return back()->with('success', 'Akun user berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate(['password' => ['required', 'confirmed', Password::min(6)]]);
        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Kata sandi akun {$user->name} berhasil direset.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return back()->with('success', 'Akun user berhasil dihapus.');
    }

    /**
     * Pastikan relasi satu-ke-satu user <-> personil konsisten.
     */
    private function linkPersonil(User $user, ?int $personilId): void
    {
        // Lepas tautan personil lama bila berubah.
        Personil::where('user_id', $user->id)->when($personilId, fn ($q) => $q->where('id', '!=', $personilId))->update(['user_id' => null]);

        if ($personilId) {
            Personil::where('id', $personilId)->update(['user_id' => $user->id]);
        }
    }
}
