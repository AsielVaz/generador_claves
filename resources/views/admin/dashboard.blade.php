<x-layouts.app title="Admin" heading="Dashboard administrativo">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Alumnos registrados</p>
            <p class="mt-3 text-3xl font-semibold">{{ $studentsCount }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Inscripciones totales</p>
            <p class="mt-3 text-3xl font-semibold">{{ $enrollmentsCount }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Cursos activos</p>
            <p class="mt-3 text-3xl font-semibold">{{ $activeCoursesCount }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Pagos pendientes</p>
            <p class="mt-3 text-3xl font-semibold">{{ $pendingPaymentsCount }}</p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Ingresos reportados</p>
            <p class="mt-3 text-3xl font-semibold">${{ number_format($reportedPaymentsTotal, 2) }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Pagos aprobados</p>
            <p class="mt-3 text-3xl font-semibold">${{ number_format($approvedPaymentsTotal, 2) }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-zinc-500">Monto condonado</p>
            <p class="mt-3 text-3xl font-semibold">${{ number_format($condonedTotal, 2) }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4">
                <h2 class="text-base font-semibold">Pagos recientes</h2>
                <a href="{{ route('admin.reports.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Ver reportes</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                        <tr>
                            <th class="px-5 py-3">Alumno</th>
                            <th class="px-5 py-3">Curso</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($recentPayments as $payment)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-medium">{{ $payment->user->name }}</p>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $payment->created_at->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-5 py-4 text-zinc-600">{{ $payment->course->title ?? 'Cartera' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-md {{ $payment->status === 'pendiente' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }} px-2.5 py-1 text-xs font-semibold">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right font-semibold">${{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-zinc-500">No hay pagos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4">
                <h2 class="text-base font-semibold">Cursos con mas inscritos</h2>
                <a href="{{ route('admin.courses.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Administrar</a>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse ($topCourses as $course)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <p class="font-medium">{{ $course->title }}</p>
                            <p class="mt-1 text-sm text-zinc-500">Costo ${{ number_format($course->course_cost ?: $course->price, 2) }}</p>
                        </div>
                        <span class="rounded-md bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">{{ $course->users_count }} inscritos</span>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-zinc-500">No hay inscripciones registradas.</div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-5 py-4">
                <h2 class="text-base font-semibold">Cierres de pago proximos</h2>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse ($paymentDeadlines as $course)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <p class="font-medium">{{ $course->title }}</p>
                            <p class="mt-1 text-sm text-zinc-500">{{ $course->users_count }} inscritos</p>
                        </div>
                        <span class="shrink-0 rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                            {{ $course->payment_end_date->format('d/m/Y') }}
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-zinc-500">No hay cierres de pago proximos.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4">
                <h2 class="text-base font-semibold">Alumnos recientes</h2>
                <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Ver usuarios</a>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse ($recentUsers as $user)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <p class="font-medium">{{ $user->name }}</p>
                            <p class="mt-1 text-sm text-zinc-500">{{ $user->email }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-zinc-500">{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-zinc-500">No hay alumnos registrados.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
