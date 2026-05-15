<x-layouts.app title="Pagos" heading="Pagos">
    <div class="mb-5 flex justify-end">
        <a href="{{ route('payments.create') }}" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Registrar pago</a>
    </div>

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                    <tr>
                        <th class="px-5 py-3">Curso</th>
                        <th class="px-5 py-3">Metodo</th>
                        <th class="px-5 py-3">Referencia</th>
                        <th class="px-5 py-3">Clave unica</th>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3 text-right">Monto</th>
                        <th class="px-5 py-3 text-right">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-5 py-4 font-medium">{{ $payment->course->title }}</td>
                            <td class="px-5 py-4 text-zinc-600">
                                {{ ucfirst($payment->method) }}
                                @if ($payment->is_condoned)
                                    <span class="ml-2 rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Condonado</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-zinc-600">{{ $payment->reference ?? 'Sin referencia' }}</td>
                            <td class="max-w-64 truncate px-5 py-4 font-mono text-xs text-zinc-500" title="{{ $payment->unica }}">{{ $payment->unica ?? 'Sin clave' }}</td>
                            <td class="px-5 py-4 text-zinc-600">{{ optional($payment->paid_at)->format('d/m/Y') ?? $payment->created_at->format('d/m/Y') }}</td>
                            <td class="px-5 py-4 text-right font-semibold">${{ number_format($payment->amount, 2) }}</td>
                            <td class="px-5 py-4 text-right"><span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ ucfirst($payment->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-zinc-500">No hay pagos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-zinc-200 px-5 py-4">
            {{ $payments->links() }}
        </div>
    </section>
</x-layouts.app>
