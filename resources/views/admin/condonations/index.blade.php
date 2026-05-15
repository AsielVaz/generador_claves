<x-layouts.app title="Condonaciones" heading="Condonaciones">
    <form method="POST" action="{{ route('admin.condonations.store') }}" class="mb-6 max-w-3xl space-y-5 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="text-sm font-medium" for="user_id">Usuario</label>
                <select id="user_id" name="user_id" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                    <option value="">Selecciona un usuario</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} · {{ $user->email }}</option>
                    @endforeach
                </select>
                @error('user_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium" for="course_id">Curso</label>
                <select id="course_id" name="course_id" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                    <option value="">Selecciona un curso</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
                @error('course_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            La condonacion genera un pago marcado como condonado y cubre automaticamente el saldo pendiente del curso.
        </div>

        <div class="flex justify-end border-t border-zinc-100 pt-5">
            <button class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white">Condonar pago</button>
        </div>
    </form>

    <section class="rounded-lg border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 px-5 py-4">
            <h2 class="font-semibold">Condonaciones recientes</h2>
        </div>
        <div class="divide-y divide-zinc-100">
            @forelse ($condonations as $payment)
                <div class="grid gap-2 px-5 py-4 text-sm md:grid-cols-4">
                    <p class="font-medium">{{ $payment->user->name }}</p>
                    <p class="text-zinc-600">{{ $payment->course->title }}</p>
                    <p class="font-semibold">${{ number_format($payment->amount, 2) }}</p>
                    <p class="text-zinc-500">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm text-zinc-500">No hay condonaciones registradas.</div>
            @endforelse
        </div>
    </section>
</x-layouts.app>
