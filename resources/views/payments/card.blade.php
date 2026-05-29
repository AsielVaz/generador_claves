<x-layouts.app title="Pago con PayPal" heading="Pago con PayPal">
    <style>
        .card-payment-layout {
            display: grid;
            grid-template-columns: minmax(260px, 0.8fr) minmax(420px, 1.2fr);
            gap: 24px;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .card-payment-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="card-payment-layout" style="display: grid; gap: 24px; align-items: start;">
        <section class="rounded-lg border border-white/70 bg-white/75 p-5 shadow-sm backdrop-blur" style="border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.7); background: rgba(255, 255, 255, 0.75); padding: 20px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); backdrop-filter: blur(18px);">
            <p class="text-sm font-medium text-emerald-700">Saldo actual en cartera</p>
            <p class="mt-2 text-3xl font-semibold text-emerald-950">${{ number_format($walletBalance, 2) }}</p>
            <p class="mt-3 text-sm text-zinc-600">El pago se acreditara en tu cartera cuando PayPal confirme y capture la orden.</p>
        </section>

        <section class="rounded-lg border border-white/70 bg-white/75 p-5 shadow-sm backdrop-blur" style="border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.7); background: rgba(255, 255, 255, 0.75); padding: 20px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); backdrop-filter: blur(18px);">
            <form method="POST" action="{{ route('payments.card.store') }}" class="grid gap-4" style="display: grid; gap: 16px;">
                @csrf

                @if ($errors->has('paypal'))
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700" style="border-radius: 6px; border: 1px solid #fecaca; background: rgba(254, 242, 242, 0.92); padding: 12px 16px; color: #b91c1c; font-size: 14px; font-weight: 600;">
                        {{ $errors->first('paypal') }}
                    </div>
                @endif

                @if (session('paypal_api_error'))
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" style="border-radius: 6px; border: 1px solid #fecaca; background: rgba(254, 242, 242, 0.92); padding: 12px 16px; color: #991b1b;">
                        <p class="font-semibold" style="margin: 0 0 8px; font-weight: 700;">Detalle tecnico de PayPal</p>
                        <pre style="margin: 0; max-height: 360px; overflow: auto; white-space: pre-wrap; word-break: break-word; font-size: 12px; line-height: 1.5;">{{ session('paypal_api_error') }}</pre>
                    </div>
                @endif

                <div>
                    <label for="amount" class="block text-sm font-medium text-zinc-700">Monto a abonar</label>
                    <input id="amount" name="amount" type="text" inputmode="decimal" data-money-input value="{{ old('amount') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200" style="margin-top: 4px; width: 100%; box-sizing: border-box; border-radius: 6px; border: 1px solid rgba(212, 212, 216, 0.95); background: rgba(255, 255, 255, 0.82); padding: 10px 12px; font-size: 14px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);" placeholder="1.00" required>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800" style="width: 100%; border: 0; border-radius: 6px; background: linear-gradient(135deg, #1f2937, #0f766e); padding: 10px 16px; font-size: 14px; font-weight: 700; color: #ffffff; box-shadow: 0 16px 32px rgba(15, 118, 110, 0.22); cursor: pointer;">Continuar a PayPal</button>
            </form>
        </section>
    </div>
</x-layouts.app>
