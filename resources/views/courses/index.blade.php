<x-layouts.app title="Cursos" heading="Cursos">
    <div class="grid gap-5 lg:grid-cols-2 2xl:grid-cols-3">
        @forelse ($courses as $course)
            @php($isEnrolled = in_array($course->id, $enrolledIds))
            <article class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $course->title }}</h2>
                        <p class="mt-2 text-sm leading-6 text-zinc-500">{{ $course->description }}</p>
                    </div>
                    <span class="shrink-0 rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">{{ $course->duration_hours }} h</span>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-zinc-100 pt-4">
                    <div>
                        <p class="text-xs text-zinc-500">Precio</p>
                        <p class="text-lg font-semibold">${{ number_format($course->price, 2) }}</p>
                    </div>
                    @if ($isEnrolled)
                        <span class="rounded-md bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">Registrado</span>
                    @else
                        <form method="POST" action="{{ route('courses.enroll', $course) }}">
                            @csrf
                            <button class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Registrarme</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center shadow-sm">
                <p class="font-medium">No hay cursos activos por el momento.</p>
            </div>
        @endforelse
    </div>
</x-layouts.app>
