<x-layouts.app title="Nuevo curso" heading="Nuevo curso">
    <form method="POST" action="{{ route('admin.courses.store') }}" class="max-w-3xl space-y-5 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="text-sm font-medium" for="title">Nombre del curso</label>
            <input id="title" name="title" value="{{ old('title') }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
            @error('title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium" for="description">Descripcion</label>
            <textarea id="description" name="description" rows="4" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
            @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="text-sm font-medium" for="start_date">Fecha inicio</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                @error('start_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium" for="end_date">Fecha fin</label>
                <input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                @error('end_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="text-sm font-medium" for="payment_start_date">Fecha inicio pago</label>
                <input id="payment_start_date" name="payment_start_date" type="date" value="{{ old('payment_start_date') }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                @error('payment_start_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium" for="payment_end_date">Fecha fin pago</label>
                <input id="payment_end_date" name="payment_end_date" type="date" value="{{ old('payment_end_date') }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                @error('payment_end_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <label class="text-sm font-medium" for="minimum_payment">Pago minimo</label>
                <input id="minimum_payment" name="minimum_payment" type="text" inputmode="decimal" data-money-input value="{{ old('minimum_payment') }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                @error('minimum_payment') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium" for="course_cost">Costo curso</label>
                <input id="course_cost" name="course_cost" type="text" inputmode="decimal" data-money-input value="{{ old('course_cost') }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                @error('course_cost') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium" for="duration_hours">Horas</label>
                <input id="duration_hours" name="duration_hours" type="number" min="1" value="{{ old('duration_hours', 1) }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                @error('duration_hours') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm font-medium">
            <input type="checkbox" name="is_active" value="1" checked class="rounded border-zinc-300">
            Curso activo
        </label>

        <div class="flex justify-end gap-3 border-t border-zinc-100 pt-5">
            <a href="{{ route('admin.courses.index') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-semibold">Cancelar</a>
            <button class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white">Guardar</button>
        </div>
    </form>
</x-layouts.app>
