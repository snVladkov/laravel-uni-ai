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
    <main class="grid min-h-screen lg:grid-cols-2">
        <section class="relative hidden overflow-hidden bg-ue-burgundy p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -right-24 -top-24 size-72 rounded-full border-[24px] border-ue-gold/20"></div>
            <div class="absolute -bottom-32 -left-20 size-96 rounded-full border-[30px] border-white/5"></div>

            <div class="relative">
                <span class="grid size-20 place-items-center rounded-full border-4 border-ue-gold bg-ue-burgundy-dark font-display text-2xl font-semibold text-ue-gold">
                    ИУ
                </span>
                <p class="mt-8 font-display text-4xl uppercase tracking-wide">{{ __('app.name') }}</p>
                <p class="mt-3 max-w-md text-lg text-white/75">{{ __('app.tagline') }}</p>
            </div>

            <div class="relative rounded-xl border border-white/15 bg-white/5 p-6">
                <p class="font-display text-xl text-ue-gold">{{ __('app.university') }}</p>
                <p class="mt-2 text-sm leading-relaxed text-white/70">{{ __('app.login_intro') }}</p>
            </div>
        </section>

        <section class="flex items-center justify-center px-4 py-12 sm:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center justify-between lg:hidden">
                    <span class="flex items-center gap-3">
                        <span class="grid size-12 place-items-center rounded-full border-2 border-ue-gold bg-ue-burgundy font-display text-lg text-ue-gold">ИУ</span>
                        <span class="font-display text-lg uppercase tracking-wide text-ue-burgundy">{{ __('app.name') }}</span>
                    </span>
                </div>

                <div class="mb-6 flex justify-end gap-1 rounded-full border border-ue-sand bg-white p-1 text-xs font-semibold shadow-sm w-fit ml-auto">
                    <a href="{{ route('language.switch', 'bg') }}"
                       class="rounded-full px-3 py-1.5 transition {{ app()->getLocale() === 'bg' ? 'bg-ue-burgundy text-white' : 'text-slate-500 hover:text-ue-burgundy' }}">BG</a>
                    <a href="{{ route('language.switch', 'en') }}"
                       class="rounded-full px-3 py-1.5 transition {{ app()->getLocale() === 'en' ? 'bg-ue-burgundy text-white' : 'text-slate-500 hover:text-ue-burgundy' }}">EN</a>
                </div>

                <div class="rounded-2xl border border-ue-sand bg-white p-8 shadow-xl shadow-ue-burgundy/5">
                    @yield('content')
                </div>

                <p class="mt-6 text-center text-xs text-slate-500">{{ __('app.login_help') }}</p>
            </div>
        </section>
    </main>
</body>
</html>
