<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email_local' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._%+-]+$/'],
            'password' => ['required', Password::min(8)],
        ]);

        $email = Str::lower($validated['email_local']).'@gmail.com';

        if (User::where('email', $email)->exists()) {
            return back()
                ->withErrors(['email_local' => 'Este correo ya esta registrado.'])
                ->withInput();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => $validated['password'],
        ]);

        enviarCorreoBienvenida($user->email, $user->name);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
