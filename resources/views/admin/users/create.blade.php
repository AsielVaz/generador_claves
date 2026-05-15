<x-layouts.app title="Nuevo usuario" heading="Nuevo usuario">
    <form method="POST" action="{{ route('admin.users.store') }}" class="max-w-xl space-y-5 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="text-sm font-medium" for="name">Nombre</label>
            <input id="name" name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
            @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium" for="email_local">Correo Gmail</label>
            <div class="mt-2 flex rounded-md border border-zinc-300 bg-white">
                <input id="email_local" name="email_local" value="{{ old('email_local') }}" required pattern="[A-Za-z0-9._%+-]+" class="min-w-0 flex-1 rounded-l-md px-3 py-2 text-sm outline-none">
                <span class="shrink-0 border-l border-zinc-200 bg-zinc-50 px-3 py-2 text-sm font-medium text-zinc-600">@gmail.com</span>
            </div>
            @error('email_local') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium" for="password">Contrasena</label>
            <input id="password" name="password" type="password" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
            @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center gap-2 text-sm font-medium">
            <input type="checkbox" name="is_admin" value="1" class="rounded border-zinc-300">
            Administrador
        </label>
        <div class="flex justify-end gap-3 border-t border-zinc-100 pt-5">
            <a href="{{ route('admin.users.index') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-semibold">Cancelar</a>
            <button class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white">Guardar</button>
        </div>
    </form>
</x-layouts.app>
