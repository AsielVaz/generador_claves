<x-layouts.app title="Mis cursos" heading="Mis cursos">
    <div class="grid gap-5 lg:grid-cols-2">
        @forelse ($courses as $course)
            @php
                $paidTotal = (float) ($course->paid_total ?? 0);
                $minimumPayment = (float) $course->minimum_payment;
                $courseCost = (float) ($course->course_cost ?: $course->price);
                $paymentPeriodEnded = $course->payment_end_date && $today > $course->payment_end_date->toDateString();
                $isUnlocked = $paidTotal >= $minimumPayment && (! $paymentPeriodEnded || $paidTotal >= $courseCost);
                $progress = $courseCost > 0 ? min(100, round(($paidTotal / $courseCost) * 100)) : 100;
            @endphp

            <article class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $course->title }}</h2>
                        <p class="mt-2 text-sm leading-6 text-zinc-500">{{ $course->description }}</p>
                    </div>
                    <span class="shrink-0 rounded-md {{ $isUnlocked ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} px-2.5 py-1 text-xs font-semibold">
                        {{ $isUnlocked ? 'Desbloqueado' : 'Bloqueado' }}
                    </span>
                </div>

                <div class="mt-5">
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium text-zinc-600">Pagado</span>
                        <span class="font-semibold">${{ number_format($paidTotal, 2) }} / ${{ number_format($courseCost, 2) }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-zinc-100">
                        <div class="h-full rounded-full {{ $isUnlocked ? 'bg-emerald-700' : 'bg-amber-500' }}" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-zinc-100 pt-4">
                    <p class="text-sm text-zinc-500">
                        Minimo: ${{ number_format($minimumPayment, 2) }}
                        @if ($paymentPeriodEnded && $paidTotal < $courseCost)
                            · Periodo de pago vencido
                        @endif
                    </p>

                    @if ($isUnlocked)
                        <a href="{{ route('courses.show', $course) }}" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Ver curso</a>
                    @else
                        <button disabled class="cursor-not-allowed rounded-md bg-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-500">Ver curso</button>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center shadow-sm">
                <p class="font-medium">Aun no estas inscrito en ningun curso.</p>
                <a href="{{ route('courses.index') }}" class="mt-4 inline-flex rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white">Explorar cursos</a>
            </div>
        @endforelse
    </div>
</x-layouts.app>
