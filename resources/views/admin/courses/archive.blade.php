<x-layouts.app title="Archivar curso" heading="Archivar curso">
    <section class="mb-6 rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold">{{ $course->title }}</h2>
                <p class="mt-2 text-sm text-zinc-500">
                    Fecha de finalizacion: {{ optional($course->end_date)->format('d/m/Y') ?? 'Sin fecha' }}
                </p>
            </div>
            <span class="self-start rounded-md {{ $shouldRefund ? 'bg-amber-50 text-amber-700' : 'bg-zinc-100 text-zinc-700' }} px-3 py-2 text-sm font-semibold">
                {{ $shouldRefund ? 'Requiere revision de reembolsos' : 'Sin reembolsos por fecha' }}
            </span>
        </div>

        <p class="mt-4 text-sm leading-6 text-zinc-600">
            Al confirmar, el curso se desactivara. Si la fecha de finalizacion aun no ha ocurrido, se cargara a la cartera de cada alumno el monto que haya pagado al curso como reembolso.
        </p>
    </section>

    @if ($shouldRefund)
        <section class="mb-6 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-5 py-4">
                <h3 class="font-semibold">Reembolsos a procesar</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                        <tr>
                            <th class="px-5 py-3">Alumno</th>
                            <th class="px-5 py-3">Correo</th>
                            <th class="px-5 py-3">Concepto</th>
                            <th class="px-5 py-3 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($refunds as $refund)
                            <tr>
                                <td class="px-5 py-4 font-medium">{{ $refund['user']->name }}</td>
                                <td class="px-5 py-4 text-zinc-600">{{ $refund['user']->email }}</td>
                                <td class="px-5 py-4 text-zinc-600">{{ $refund['reference'] }}</td>
                                <td class="px-5 py-4 text-right font-semibold text-emerald-700">${{ number_format($refund['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-zinc-500">No hay pagos de alumnos para reembolsar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <form method="POST" action="{{ route('admin.courses.archive', $course) }}" class="flex justify-end gap-3">
        @csrf
        @method('PATCH')
        <a href="{{ route('admin.courses.index') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">Cancelar</a>
        <button class="rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
            {{ $shouldRefund && $refunds->isNotEmpty() ? 'Archivar y procesar reembolsos' : 'Archivar curso' }}
        </button>
    </form>
</x-layouts.app>
