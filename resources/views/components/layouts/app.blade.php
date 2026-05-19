@props(['title' => null, 'heading' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' | Cryptoefectivo' : config('app.name', 'Cryptoefectivo') }}</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link rel="stylesheet" href="{{ asset('css/glass.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
    @endif
</head>
<body class="glass-app min-h-screen bg-zinc-100 text-zinc-950 antialiased">
    <div class="min-h-screen lg:flex">
        @auth
            <aside class="border-b border-zinc-200 bg-white lg:fixed lg:inset-y-0 lg:left-0 lg:w-72 lg:border-b-0 lg:border-r">
                <div class="flex h-full flex-col px-5 py-5">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <span class="flex h-12 w-16 items-center justify-center overflow-hidden rounded-lg border border-zinc-200 bg-white">
                            <img src="{{ asset('logo.svg') }}" alt="Cryptoefectivo" class="h-full w-full object-contain p-1">
                        </span>
                        <span>
                            <span class="block text-sm font-semibold">Cryptoefectivo</span>
                            <span class="block text-xs text-zinc-500">Panel de cursos</span>
                        </span>
                    </a>

                    <nav class="mt-6 flex gap-2 overflow-x-auto lg:flex-col lg:overflow-visible">
                        <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard', 'admin.dashboard') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">Dashboard</a>
                        @if (! auth()->user()->is_admin)
                            <a href="{{ route('courses.index') }}" class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('courses.index') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">Cursos</a>
                            <a href="{{ route('courses.mine') }}" class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('courses.mine', 'courses.show') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">Mis cursos</a>
                            <a href="{{ route('payments.index') }}" class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('payments.*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">Pagos</a>
                        @else
                            <a href="{{ route('admin.users.index') }}" class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">Usuarios</a>
                            <a href="{{ route('admin.courses.index') }}" class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.courses.*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">Cursos admin</a>
                            <a href="{{ route('admin.condonations.index') }}" class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.condonations.*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">Condonaciones</a>
                            <a href="{{ route('admin.reports.index') }}" class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-zinc-950 text-white' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">Reportes</a>
                        @endif
                    </nav>

                    <div class="mt-auto hidden border-t border-zinc-200 pt-5 lg:block">
                        <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-zinc-500">{{ auth()->user()->email }}</p>
                        <form method="POST" action="{{ route('logout') }}" class="mt-4">
                            @csrf
                            <button class="w-full rounded-md border border-zinc-300 px-3 py-2 text-left text-sm font-medium text-zinc-700 hover:bg-zinc-100">Cerrar sesion</button>
                        </form>
                    </div>
                </div>
            </aside>
        @endauth

        <main class="@auth lg:ml-72 @endauth min-h-screen flex-1">
            @auth
                <header class="border-b border-zinc-200 bg-white">
                    <div class="flex items-center justify-between px-5 py-4 sm:px-8">
                        <div>
                            <p class="text-xs font-semibold uppercase text-emerald-700">Cryptoefectivo</p>
                            <h1 class="mt-1 text-2xl font-semibold tracking-normal">{{ $heading ?? 'Dashboard' }}</h1>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                            @csrf
                            <button class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium">Salir</button>
                        </form>
                    </div>
                </header>
            @endauth

            <div class="@auth px-5 py-6 sm:px-8 @endauth">
                @if (session('status'))
                    <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->has('course'))
                    <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        {{ $errors->first('course') }}
                    </div>
                @endif

                @if ($errors->has('admin') || $errors->has('condonation'))
                    <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        {{ $errors->first('admin') ?: $errors->first('condonation') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
