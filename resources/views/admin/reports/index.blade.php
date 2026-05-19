<x-layouts.app title="Reportes" heading="Reportes">
    <section class="mb-6 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 px-5 py-4">
            <h2 class="font-semibold">Inscritos por curso</h2>
        </div>

        <div class="divide-y divide-zinc-100">
            @forelse ($enrollmentReport as $report)
                <div class="px-5 py-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-semibold">{{ $report['course']->title }}</h3>
                            <p class="mt-1 text-sm text-zinc-500">
                                Costo ${{ number_format($report['course']->course_cost ?: $report['course']->price, 2) }}
                            </p>
                        </div>
                        <span class="self-start rounded-md bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 sm:self-auto">
                            {{ $report['students_count'] }} inscritos
                        </span>
                    </div>

                    @if ($report['students']->isNotEmpty())
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                                    <tr>
                                        <th class="px-4 py-2">Alumno</th>
                                        <th class="px-4 py-2">Correo</th>
                                        <th class="px-4 py-2">Fecha inscripcion</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100">
                                    @foreach ($report['students'] as $student)
                                        <tr>
                                            <td class="px-4 py-3 font-medium">{{ $student->name }}</td>
                                            <td class="px-4 py-3 text-zinc-600">{{ $student->email }}</td>
                                            <td class="px-4 py-3 text-zinc-600">
                                                {{ $student->pivot->enrolled_at ? \Illuminate\Support\Carbon::parse($student->pivot->enrolled_at)->format('d/m/Y H:i') : 'Sin fecha' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-zinc-500">Este curso aun no tiene inscritos.</p>
                    @endif
                </div>
            @empty
                <div class="px-5 py-10 text-center text-zinc-500">No hay cursos registrados.</div>
            @endforelse
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 px-5 py-4">
            <h2 class="font-semibold">Inscripciones y pagos</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                    <tr>
                        <th class="px-5 py-3">Alumno</th>
                        <th class="px-5 py-3">Correo</th>
                        <th class="px-5 py-3">Curso</th>
                        <th class="px-5 py-3 text-right">Costo</th>
                        <th class="px-5 py-3 text-right">Pagado</th>
                        <th class="px-5 py-3 text-right">Pendiente</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($rows as $row)
                        <tr>
                            <td class="px-5 py-4 font-medium">{{ $row['user']->name }}</td>
                            <td class="px-5 py-4 text-zinc-600">{{ $row['user']->email }}</td>
                            <td class="px-5 py-4 text-zinc-600">{{ $row['course']->title }}</td>
                            <td class="px-5 py-4 text-right font-semibold">${{ number_format($row['course_cost'], 2) }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-emerald-700">${{ number_format($row['paid_total'], 2) }}</td>
                            <td class="px-5 py-4 text-right font-semibold {{ $row['remaining'] > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                ${{ number_format($row['remaining'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-zinc-500">No hay inscripciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
