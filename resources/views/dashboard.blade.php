<x-layouts.app title="Dashboard" heading="Dashboard">
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Cursos disponibles</p>
            <p class="mt-3 text-3xl font-semibold">{{ $availableCourses }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Cursos registrados</p>
            <p class="mt-3 text-3xl font-semibold">{{ $enrolledCoursesCount }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Saldo en cartera</p>
            <p class="mt-3 text-3xl font-semibold">${{ number_format($walletBalance, 2) }}</p>
            <p class="mt-2 text-xs text-zinc-500">Cargado: ${{ number_format($paymentsTotal, 2) }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4">
                <h2 class="text-base font-semibold">Mis cursos</h2>
                <a href="{{ route('courses.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Ver cursos</a>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse ($enrolledCourses as $course)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold">{{ $course->title }}</h3>
                                <p class="mt-1 line-clamp-2 text-sm text-zinc-500">{{ $course->description }}</p>
                            </div>
                            <span class="shrink-0 rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ $course->duration_hours }} h</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="font-medium">Aun no tienes cursos registrados.</p>
                        <a href="{{ route('courses.index') }}" class="mt-3 inline-flex rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white">Explorar cursos</a>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4">
                <h2 class="text-base font-semibold">Pagos recientes</h2>
                <a href="{{ route('payments.create') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Registrar pago</a>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse ($recentPayments as $payment)
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-medium">{{ $payment->course->title ?? 'Cartera' }}</p>
                                <p class="mt-1 text-sm text-zinc-500">{{ ucfirst($payment->method) }} · {{ $payment->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">${{ number_format($payment->amount, 2) }}</p>
                                <p class="mt-1 text-xs font-semibold text-amber-700">{{ ucfirst($payment->status) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-zinc-500">No hay pagos registrados.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
