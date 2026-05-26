<x-layouts.app title="Pago con tarjeta" heading="Pago con tarjeta">
    <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <section class="rounded-lg border border-white/70 bg-white/75 p-5 shadow-sm backdrop-blur">
            <p class="text-sm font-medium text-emerald-700">Saldo actual en cartera</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-950">${{ number_format($walletBalance, 2) }}</p>
            <p class="mt-3 text-sm text-zinc-600">El pago se acreditara en tu cartera cuando Espiral confirme la transaccion.</p>
        </section>

        <section class="rounded-lg border border-white/70 bg-white/75 p-5 shadow-sm backdrop-blur">
            <form method="POST" action="{{ route('payments.card.store') }}" class="grid gap-4">
                @csrf

                @if ($errors->has('espiral'))
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        {{ $errors->first('espiral') }}
                    </div>
                @endif

                <div>
                    <label for="amount" class="block text-sm font-medium text-zinc-700">Monto a abonar</label>
                    <input id="amount" name="amount" type="text" inputmode="decimal" data-money-input value="{{ old('amount') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="1.00" required>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-zinc-700">Telefono</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="street" class="block text-sm font-medium text-zinc-700">Calle</label>
                        <input id="street" name="street" type="text" value="{{ old('street') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                        @error('street')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="number_ext" class="block text-sm font-medium text-zinc-700">Numero exterior</label>
                        <input id="number_ext" name="number_ext" type="text" value="{{ old('number_ext') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                        @error('number_ext')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="number_int" class="block text-sm font-medium text-zinc-700">Numero interior</label>
                        <input id="number_int" name="number_int" type="text" value="{{ old('number_int') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                        @error('number_int')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="zip_code" class="block text-sm font-medium text-zinc-700">Codigo postal</label>
                        <input id="zip_code" name="zip_code" type="text" value="{{ old('zip_code') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                        @error('zip_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-zinc-700">Ciudad</label>
                        <input id="city" name="city" type="text" value="{{ old('city') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="state" class="block text-sm font-medium text-zinc-700">Estado</label>
                        <input id="state" name="state" type="text" value="{{ old('state') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" required>
                        @error('state')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">Continuar a Espiral</button>
            </form>
        </section>
    </div>
</x-layouts.app>
