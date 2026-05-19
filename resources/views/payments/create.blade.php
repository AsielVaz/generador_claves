<x-layouts.app title="Registrar pago" heading="Registrar pago">
    <div class="max-w-2xl rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="space-y-5" data-payment-upload-form data-payment-preview-url="{{ route('payments.preview') }}">
            @csrf
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3">
                <p class="text-sm text-emerald-800">Saldo actual en cartera</p>
                <p class="mt-1 text-2xl font-semibold text-emerald-950">${{ number_format($walletBalance, 2) }}</p>
            </div>

            <div>
                <label for="payment_file" class="text-sm font-medium">Archivo 10hf</label>
                <input id="payment_file" name="payment_file" type="file" accept=".10hf" required class="mt-2 w-full rounded-md border border-dashed border-zinc-300 bg-zinc-50 px-3 py-3 text-sm outline-none file:mr-4 file:rounded-md file:border-0 file:bg-zinc-950 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:bg-zinc-100 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">
                @error('payment_file') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                @if (session('decrypted_payment_content'))
                    <pre class="mt-3 max-h-64 overflow-auto whitespace-pre-wrap rounded-md border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">{{ session('decrypted_payment_content') }}</pre>
                @endif
            </div>

            <section class="hidden rounded-lg border border-zinc-200 bg-zinc-50 p-4" data-payment-preview>
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <h2 class="text-sm font-semibold">Datos del pago realizado</h2>
                        <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Archivo valido</span>
                    </div>
                    <dl class="grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-zinc-500">Monto</dt>
                            <dd class="mt-1 font-semibold" data-preview-field="amount">-</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Metodo</dt>
                            <dd class="mt-1 font-semibold" data-preview-field="method">-</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Estado</dt>
                            <dd class="mt-1 font-semibold" data-preview-field="status">-</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500">Fecha de pago</dt>
                            <dd class="mt-1 font-semibold" data-preview-field="paid_at">-</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-zinc-500">Referencia</dt>
                            <dd class="mt-1 font-semibold" data-preview-field="reference">Sin referencia</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-zinc-500">Clave unica</dt>
                            <dd class="mt-1 break-all font-semibold" data-preview-field="unica">-</dd>
                        </div>
                    </dl>
            </section>

            <div class="hidden whitespace-pre-wrap rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700" data-payment-preview-error></div>

            <div class="flex justify-end gap-3 border-t border-zinc-100 pt-5">
                <a href="{{ route('payments.index') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">Cancelar</a>
                <button disabled data-payment-submit class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800 disabled:cursor-not-allowed disabled:bg-zinc-300 disabled:text-zinc-500">Cargar saldo</button>
            </div>
        </form>
    </div>
</x-layouts.app>
