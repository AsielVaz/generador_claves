<x-layouts.app title="Iniciar sesion">
    <div class="grid min-h-screen place-items-center px-5 py-10">
        <div class="w-full max-w-md rounded-lg border border-zinc-200 bg-white p-8 shadow-sm">
            <div class="mb-8">
                <div class="flex h-20 w-32 items-center justify-center overflow-hidden rounded-lg border border-zinc-200 bg-white">
                    <img src="{{ asset('logo.svg') }}" alt="Cryptoefectivo" class="h-full w-full object-contain p-2">
                </div>
                <h1 class="mt-5 text-2xl font-semibold">Iniciar sesion</h1>
                <p class="mt-2 text-sm text-zinc-500">Accede a Cryptoefectivo.</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="text-sm font-medium">Correo</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">
                    @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="text-sm font-medium">Contrasena</label>
                    <input id="password" name="password" type="password" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">
                    @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-zinc-600">
                    <input type="checkbox" name="remember" class="rounded border-zinc-300 text-emerald-700 focus:ring-emerald-700">
                    Recordarme
                </label>

                <button class="w-full rounded-md bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">Entrar</button>
            </form>

            <p class="mt-6 text-center text-sm text-zinc-600">
                No tienes cuenta?
                <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Registrate</a>
            </p>
        </div>
    </div>
</x-layouts.app>
