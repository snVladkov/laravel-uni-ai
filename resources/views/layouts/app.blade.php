<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ue-cream text-ue-slate">
    <div class="min-h-screen">
        <header class="bg-ue-burgundy text-white shadow-lg">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="grid size-11 place-items-center rounded-full border-2 border-ue-gold bg-ue-burgundy-dark font-display text-lg font-semibold tracking-wide text-ue-gold">
                        ИУ
                    </span>
                    <span class="leading-tight">
                        <span class="block font-display text-lg uppercase tracking-wide">{{ __('app.name') }}</span>
                        <span class="block text-xs text-white/70">{{ __('app.tagline') }}</span>
                    </span>
                </a>

                <div class="flex items-center gap-3">
                    <nav class="hidden items-center gap-1 lg:flex" aria-label="{{ __('app.main_navigation') }}">
                        <a href="{{ route('dashboard') }}"
                           class="rounded-md px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white' : '' }}">
                            {{ __('app.dashboard') }}
                        </a>

                        @can('viewAny', App\Models\Course::class)
                            <a href="{{ route('courses.index') }}"
                               class="rounded-md px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('courses.*') ? 'bg-white/10 text-white' : '' }}">
                                {{ __('app.courses') }}
                            </a>
                        @endcan

                        @can('courses.review')
                            <a href="{{ route('hod.courses.pending') }}"
                               class="rounded-md px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('hod.courses.*') ? 'bg-white/10 text-white' : '' }}">
                                {{ __('app.pending_approvals') }}
                            </a>
                        @endcan

                        @can('manage-teachers')
                            <a href="{{ route('hod.teachers.index') }}"
                               class="rounded-md px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('hod.teachers.*') ? 'bg-white/10 text-white' : '' }}">
                                {{ __('app.teachers') }}
                            </a>
                        @endcan

                        @can('viewAny', App\Models\User::class)
                            <a href="{{ route('admin.users.index') }}"
                               class="rounded-md px-3 py-2 text-sm font-medium text-white/80 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white' : '' }}">
                                {{ __('app.users') }}
                            </a>
                        @endcan
                    </nav>

                    <div class="flex items-center gap-1 rounded-full border border-white/20 p-1 text-xs font-semibold">
                        <a href="{{ route('language.switch', 'bg') }}"
                           class="rounded-full px-2 py-1 transition {{ app()->getLocale() === 'bg' ? 'bg-ue-gold text-ue-burgundy-dark' : 'text-white/70 hover:text-white' }}">BG</a>
                        <a href="{{ route('language.switch', 'en') }}"
                           class="rounded-full px-2 py-1 transition {{ app()->getLocale() === 'en' ? 'bg-ue-gold text-ue-burgundy-dark' : 'text-white/70 hover:text-white' }}">EN</a>
                    </div>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-md px-2 py-1.5 text-sm text-white/90 marker:hidden hover:bg-white/10">
                            <span class="grid size-8 place-items-center rounded-full bg-ue-gold font-semibold text-ue-burgundy-dark">
                                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                        </summary>
                        <div class="absolute right-0 z-20 mt-2 w-56 rounded-lg border border-ue-sand bg-white p-2 text-ue-slate shadow-xl">
                            <p class="px-3 py-2 text-xs text-slate-500">{{ auth()->user()->email }}</p>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-md px-3 py-2 text-left text-sm font-medium text-ue-crimson hover:bg-red-50">
                                    {{ __('auth.logout') }}
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
            </div>

            <nav class="flex gap-1 overflow-x-auto border-t border-white/10 px-4 py-2 lg:hidden" aria-label="{{ __('app.main_navigation') }}">
                <a href="{{ route('dashboard') }}" class="whitespace-nowrap rounded-md px-3 py-1.5 text-sm text-white/80 hover:bg-white/10">{{ __('app.dashboard') }}</a>
                @can('viewAny', App\Models\Course::class)
                    <a href="{{ route('courses.index') }}" class="whitespace-nowrap rounded-md px-3 py-1.5 text-sm text-white/80 hover:bg-white/10">{{ __('app.courses') }}</a>
                @endcan
                @can('courses.review')
                    <a href="{{ route('hod.courses.pending') }}" class="whitespace-nowrap rounded-md px-3 py-1.5 text-sm text-white/80 hover:bg-white/10">{{ __('app.pending_approvals') }}</a>
                @endcan
                @can('manage-teachers')
                    <a href="{{ route('hod.teachers.index') }}" class="whitespace-nowrap rounded-md px-3 py-1.5 text-sm text-white/80 hover:bg-white/10">{{ __('app.teachers') }}</a>
                @endcan
                @can('viewAny', App\Models\User::class)
                    <a href="{{ route('admin.users.index') }}" class="whitespace-nowrap rounded-md px-3 py-1.5 text-sm text-white/80 hover:bg-white/10">{{ __('app.users') }}</a>
                @endcan
            </nav>
        </header>

        <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="status">
                    {{ session('warning') }}
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="border-t border-ue-sand py-6 text-center text-xs text-slate-500">
            {{ __('app.footer', ['year' => now()->year]) }}
        </footer>
    </div>
</body>
</html>
