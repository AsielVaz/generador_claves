<x-layouts.app title="Flujo de efectivo" heading="Flujo de efectivo">
    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-5">
        <p class="text-sm text-emerald-800">Total acreditado a carteras</p>
        <p class="mt-1 text-3xl font-semibold text-emerald-950">${{ number_format($totalCredits, 2) }}</p>
    </div>

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 px-5 py-4">
            <h2 class="font-semibold">Pagos acreditados a saldo de cartera</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                    <tr>
                        <th class="px-5 py-3">Alumno</th>
                        <th class="px-5 py-3">Correo</th>
                        <th class="px-5 py-3">Metodo</th>
                        <th class="px-5 py-3">Referencia</th>
                        <th class="px-5 py-3">Clave unica</th>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($credits as $credit)
                        <tr>
                            <td class="px-5 py-4 font-medium">{{ $credit->user->name ?? 'Usuario eliminado' }}</td>
                            <td class="px-5 py-4 text-zinc-600">{{ $credit->user->email ?? 'Sin correo' }}</td>
                            <td class="px-5 py-4 text-zinc-600">{{ ucfirst($credit->method) }}</td>
                            <td class="px-5 py-4 text-zinc-600">{{ $credit->reference ?? 'Sin referencia' }}</td>
                            <td class="max-w-64 truncate px-5 py-4 font-mono text-xs text-zinc-500" title="{{ $credit->unica }}">{{ $credit->unica ?? 'Sin clave' }}</td>
                            <td class="px-5 py-4 text-zinc-600">{{ optional($credit->paid_at)->format('d/m/Y') ?? $credit->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-emerald-700">${{ number_format($credit->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-zinc-500">No hay saldos acreditados a cartera.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-200 px-5 py-4">
            {{ $credits->links() }}
        </div>
    </section>
</x-layouts.app>
