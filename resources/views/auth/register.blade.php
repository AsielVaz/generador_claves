<x-layouts.app title="Registro">
    <div class="grid min-h-screen place-items-center px-5 py-10">
        <div class="w-full max-w-md rounded-lg border border-zinc-200 bg-white p-8 shadow-sm">
            <div class="mb-8">
                <div class="mx-auto flex h-28 w-28 items-center justify-center overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
                    <img src="{{ asset('logo.svg') }}" alt="Cryptoefectivo" class="h-full w-full object-contain">
                </div>
                <h1 class="mt-5 text-center text-2xl font-semibold">Crear cuenta</h1>
                <p class="mt-2 text-center text-sm text-zinc-500">Registrate en Cryptoefectivo con tu cuenta Gmail.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="name" class="text-sm font-medium">Nombre</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">
                    @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email_local" class="text-sm font-medium">Correo Gmail</label>
                    <div class="mt-2 flex rounded-md border border-zinc-300 bg-white focus-within:border-emerald-700 focus-within:ring-2 focus-within:ring-emerald-100">
                        <input id="email_local" name="email_local" type="text" value="{{ old('email_local') }}" required class="min-w-0 flex-1 rounded-l-md px-3 py-2 text-sm outline-none" pattern="[A-Za-z0-9._%+-]+" autocomplete="username">
                        <span class="shrink-0 border-l border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-medium text-zinc-600">@gmail.com</span>
                    </div>
                    @error('email_local') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="text-sm font-medium">Contrasena</label>
                    <input id="password" name="password" type="password" required data-password-field class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">
                    @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="text-sm font-medium">Confirmar contrasena</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required data-password-field class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">
                </div>

                <button type="button" data-password-toggle class="w-full rounded-md border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">Ver contrasenas</button>

                <button class="w-full rounded-md bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">Crear cuenta</button>
            </form>

            <p class="mt-6 text-center text-sm text-zinc-600">
                Ya tienes cuenta?
                <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Inicia sesion</a>
            </p>
        </div>
    </div>
</x-layouts.app>
