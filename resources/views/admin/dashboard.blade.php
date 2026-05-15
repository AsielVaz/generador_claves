<x-layouts.app title="Admin" heading="Administracion">
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Usuarios</p>
            <p class="mt-3 text-3xl font-semibold">{{ $usersCount }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Administradores</p>
            <p class="mt-3 text-3xl font-semibold">{{ $adminsCount }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Cursos</p>
            <p class="mt-3 text-3xl font-semibold">{{ $coursesCount }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Condonado</p>
            <p class="mt-3 text-3xl font-semibold">${{ number_format($condonedTotal, 2) }}</p>
        </div>
    </div>
</x-layouts.app>
