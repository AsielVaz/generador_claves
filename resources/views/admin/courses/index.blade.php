<x-layouts.app title="Cursos admin" heading="Cursos">
    <div class="mb-5 flex justify-end">
        <a href="{{ route('admin.courses.create') }}" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white">Nuevo curso</a>
    </div>

    <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                    <tr>
                        <th class="px-5 py-3">Curso</th>
                        <th class="px-5 py-3">Fechas</th>
                        <th class="px-5 py-3">Pago</th>
                        <th class="px-5 py-3">Inscritos</th>
                        <th class="px-5 py-3 text-right">Accion</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($courses as $course)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-medium">{{ $course->title }}</p>
                                <p class="mt-1 text-xs {{ $course->is_active ? 'text-emerald-700' : 'text-zinc-500' }}">{{ $course->is_active ? 'Activo' : 'Inactivo' }}</p>
                            </td>
                            <td class="px-5 py-4 text-zinc-600">
                                {{ optional($course->start_date)->format('d/m/Y') ?? 'Sin fecha' }} - {{ optional($course->end_date)->format('d/m/Y') ?? 'Sin fecha' }}
                            </td>
                            <td class="px-5 py-4 text-zinc-600">
                                Min. ${{ number_format($course->minimum_payment, 2) }} - Costo ${{ number_format($course->course_cost ?: $course->price, 2) }}
                            </td>
                            <td class="px-5 py-4 font-semibold">{{ $course->users_count }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.courses.show', $course) }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">Ver detalles</a>
                                    <a href="{{ route('admin.courses.edit', $course) }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-zinc-200 px-5 py-4">{{ $courses->links() }}</div>
    </section>
</x-layouts.app>
