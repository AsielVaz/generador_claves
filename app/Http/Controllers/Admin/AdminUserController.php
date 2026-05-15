<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::orderByDesc('is_admin')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email_local' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._%+-]+$/'],
            'password' => ['required', Password::min(5)],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $email = Str::lower($validated['email_local']).'@gmail.com';

        if (User::where('email', $email)->exists()) {
            return back()
                ->withErrors(['email_local' => 'Este correo ya esta registrado.'])
                ->withInput();
        }

        User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => $validated['password'],
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function toggleAdmin(Request $request, User $user): RedirectResponse
    {
        if ($user->email === 'admin@admin') {
            return back()->withErrors(['admin' => 'El administrador principal no puede modificarse.']);
        }

        $user->update([
            'is_admin' => ! $user->is_admin,
        ]);

        return back()->with('status', 'Permisos actualizados correctamente.');
    }
}
