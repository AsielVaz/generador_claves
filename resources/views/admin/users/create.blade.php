<x-layouts.app title="Nuevo usuario" heading="Nuevo usuario">
    <form method="POST" action="{{ route('admin.users.store') }}" class="max-w-xl space-y-5 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="text-sm font-medium" for="name">Nombre</label>
            <input id="name" name="name" value="{{ old('name') }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
            @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium" for="email">Correo</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
            @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
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
