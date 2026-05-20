<x-layouts.app title="Flujo activo" heading="Flujo activo">
    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-5">
        <p class="text-sm text-emerald-800">Total recaudado en cursos activos</p>
        <p class="mt-1 text-3xl font-semibold text-emerald-950">${{ number_format($totalCollected, 2) }}</p>
    </div>

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 px-5 py-4">
            <h2 class="font-semibold">Cursos no archivados y recaudacion</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                    <tr>
                        <th class="px-5 py-3">Curso</th>
                        <th class="px-5 py-3">Fechas</th>
                        <th class="px-5 py-3 text-right">Inscritos</th>
                        <th class="px-5 py-3 text-right">Recaudado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-5 py-4 font-medium">{{ $row['course']->title }}</td>
                            <td class="px-5 py-4 text-zinc-600">
                                {{ optional($row['course']->start_date)->format('d/m/Y') ?? 'Sin fecha' }} - {{ optional($row['course']->end_date)->format('d/m/Y') ?? 'Sin fecha' }}
                            </td>
                            <td class="px-5 py-4 text-right font-semibold">{{ $row['students_count'] }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-emerald-700">${{ number_format($row['collected_total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-zinc-500">No hay cursos activos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
