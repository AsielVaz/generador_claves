<x-layouts.app title="{{ $course->title }}" heading="{{ $course->title }}">
    <section class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Curso desbloqueado</span>
                <h2 class="mt-4 text-2xl font-semibold">{{ $course->title }}</h2>
                <p class="mt-3 leading-7 text-zinc-600">{{ $course->description }}</p>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 lg:w-64">
                <p class="text-sm text-zinc-500">Total pagado</p>
                <p class="mt-2 text-2xl font-semibold">${{ number_format($paidTotal, 2) }}</p>
                <p class="mt-4 text-sm text-zinc-500">Duracion</p>
                <p class="mt-1 font-semibold">{{ $course->duration_hours }} horas</p>
                <p class="mt-4 text-sm text-zinc-500">Costo del curso</p>
                <p class="mt-1 font-semibold">${{ number_format($course->course_cost ?: $course->price, 2) }}</p>
            </div>
        </div>

        <div class="mt-8 border-t border-zinc-200 pt-6">
            <h3 class="text-lg font-semibold">Informacion del curso</h3>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-zinc-200 p-4">
                    <p class="text-sm font-semibold">Modulo 1</p>
                    <p class="mt-2 text-sm text-zinc-500">Introduccion, objetivos y recursos iniciales.</p>
                </div>
                <div class="rounded-lg border border-zinc-200 p-4">
                    <p class="text-sm font-semibold">Modulo 2</p>
                    <p class="mt-2 text-sm text-zinc-500">Practicas guiadas y material complementario.</p>
                </div>
                <div class="rounded-lg border border-zinc-200 p-4">
                    <p class="text-sm font-semibold">Modulo 3</p>
                    <p class="mt-2 text-sm text-zinc-500">Evaluacion final y cierre del curso.</p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
