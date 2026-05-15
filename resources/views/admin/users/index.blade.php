<x-layouts.app title="Usuarios" heading="Usuarios">
    <div class="mb-5 flex justify-end">
        <a href="{{ route('admin.users.create') }}" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white">Nuevo usuario</a>
    </div>

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-zinc-200 text-sm">
            <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                <tr>
                    <th class="px-5 py-3">Nombre</th>
                    <th class="px-5 py-3">Correo</th>
                    <th class="px-5 py-3">Rol</th>
                    <th class="px-5 py-3 text-right">Accion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-5 py-4 font-medium">{{ $user->name }}</td>
                        <td class="px-5 py-4 text-zinc-600">{{ $user->email }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-md {{ $user->is_admin ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-700' }} px-2.5 py-1 text-xs font-semibold">
                                {{ $user->is_admin ? 'Administrador' : 'Usuario' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if ($user->email !== 'admin@admin')
                                <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                                        {{ $user->is_admin ? 'Quitar admin' : 'Hacer admin' }}
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-zinc-500">Principal</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="border-t border-zinc-200 px-5 py-4">{{ $users->links() }}</div>
    </section>
</x-layouts.app>
