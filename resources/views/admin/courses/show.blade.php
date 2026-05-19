<x-layouts.app title="Detalle del curso" heading="Detalle del curso">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold">{{ $course->title }}</h2>
            <p class="mt-1 text-sm text-zinc-500">{{ $rows->count() }} alumnos inscritos</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.courses.export', $course) }}" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white">Descargar Excel</a>
            <a href="{{ route('admin.courses.index') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">Volver</a>
        </div>
    </div>

    <section class="mb-6 rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <p class="text-sm text-zinc-500">Costo</p>
                <p class="mt-1 text-xl font-semibold">${{ number_format($course->course_cost ?: $course->price, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">Pago minimo</p>
                <p class="mt-1 text-xl font-semibold">${{ number_format($course->minimum_payment, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">Inicio</p>
                <p class="mt-1 text-xl font-semibold">{{ optional($course->start_date)->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">Fin</p>
                <p class="mt-1 text-xl font-semibold">{{ optional($course->end_date)->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 px-5 py-4">
            <h3 class="font-semibold">Alumnos inscritos</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                    <tr>
                        <th class="px-5 py-3">Alumno</th>
                        <th class="px-5 py-3">Correo</th>
                        <th class="px-5 py-3">Fecha inscripcion</th>
                        <th class="px-5 py-3 text-right">Pagado</th>
                        <th class="px-5 py-3 text-right">Pendiente</th>
                        <th class="px-5 py-3 text-right">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-5 py-4 font-medium">{{ $row['user']->name }}</td>
                            <td class="px-5 py-4 text-zinc-600">{{ $row['user']->email }}</td>
                            <td class="px-5 py-4 text-zinc-600">
                                {{ $row['enrolled_at'] ? \Illuminate\Support\Carbon::parse($row['enrolled_at'])->format('d/m/Y H:i') : 'Sin fecha' }}
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-emerald-700">${{ number_format($row['paid_total'], 2) }}</td>
                            <td class="px-5 py-4 text-right font-semibold {{ $row['remaining'] > 0 ? 'text-amber-700' : 'text-emerald-700' }}">${{ number_format($row['remaining'], 2) }}</td>
                            <td class="px-5 py-4 text-right">
                                <span class="rounded-md {{ $row['remaining'] > 0 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }} px-2.5 py-1 text-xs font-semibold">{{ $row['status'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-zinc-500">Este curso aun no tiene alumnos inscritos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
