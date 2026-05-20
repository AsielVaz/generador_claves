<x-layouts.app title="Saldo sin usar" heading="Saldo sin usar">
    <div class="mb-5 grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-sm text-emerald-800">Saldo activo sin usar</p>
            <p class="mt-1 text-3xl font-semibold text-emerald-950">${{ number_format($totalUnused, 2) }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5">
            <p class="text-sm text-zinc-600">Total abonado</p>
            <p class="mt-1 text-3xl font-semibold text-zinc-950">${{ number_format($totalCredited, 2) }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5">
            <p class="text-sm text-zinc-600">Total gastado</p>
            <p class="mt-1 text-3xl font-semibold text-zinc-950">${{ number_format($totalSpent, 2) }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 px-5 py-4">
            <h2 class="font-semibold">Usuarios con saldo activo en cartera</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                    <tr>
                        <th class="px-5 py-3">Usuario</th>
                        <th class="px-5 py-3">Correo</th>
                        <th class="px-5 py-3 text-right">Abonado</th>
                        <th class="px-5 py-3 text-right">Gastado</th>
                        <th class="px-5 py-3 text-right">Saldo sin usar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-5 py-4 font-medium">{{ $row['user']->name }}</td>
                            <td class="px-5 py-4 text-zinc-600">{{ $row['user']->email }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-emerald-700">${{ number_format($row['total_credited'], 2) }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-zinc-700">${{ number_format($row['total_spent'], 2) }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-emerald-700">${{ number_format($row['unused_balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-zinc-500">No hay usuarios con saldo activo sin usar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
