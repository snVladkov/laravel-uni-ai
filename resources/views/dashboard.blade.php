@extends('layouts.app')

@section('title', __('app.dashboard'))

@section('content')
    <div class="space-y-8">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-ue-crimson">{{ __('app.dashboard') }}</p>
                <h1 class="mt-1 font-display text-3xl uppercase tracking-wide text-ue-burgundy">
                    {{ __('dashboard.welcome', ['name' => auth()->user()->name]) }}
                </h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('dashboard.intro') }}</p>
            </div>

            <div class="flex flex-wrap gap-1.5">
                @foreach (auth()->user()->getRoleNames() as $role)
                    <span class="rounded-full bg-ue-burgundy px-3 py-1 text-xs font-semibold uppercase tracking-wide text-ue-gold">
                        {{ __('roles.'.$role) }}
                    </span>
                @endforeach
            </div>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                @php
                    $toneClasses = match ($metric['tone']) {
                        'crimson' => 'border-ue-crimson/30 bg-ue-crimson/5 text-ue-crimson',
                        'gold' => 'border-ue-gold/40 bg-ue-gold/10 text-ue-burgundy',
                        'slate' => 'border-ue-slate/20 bg-ue-slate/5 text-ue-slate',
                        default => 'border-ue-burgundy/20 bg-ue-burgundy/5 text-ue-burgundy',
                    };
                @endphp

                <div class="rounded-xl border border-ue-sand bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $metric['label'] }}</p>
                    <p class="mt-2 font-display text-4xl {{ $toneClasses }} inline-block rounded-lg px-2 py-0.5">
                        {{ $metric['value'] }}
                    </p>

                    @if ($metric['hint'])
                        <p class="mt-2 text-xs text-slate-500">{{ $metric['hint'] }}</p>
                    @endif

                    @if ($metric['url'])
                        <a href="{{ $metric['url'] }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-ue-burgundy hover:gap-2">
                            {{ __('dashboard.open') }} <span aria-hidden="true">→</span>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <section class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm">
            <h2 class="font-display text-xl uppercase tracking-wide text-ue-burgundy">{{ __('dashboard.quick_actions') }}</h2>

            <div class="mt-4 flex flex-wrap gap-3">
                @can('create', App\Models\Course::class)
                    <a href="{{ route('courses.create') }}" class="rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white hover:bg-ue-burgundy-dark">
                        {{ __('courses.index.new') }}
                    </a>
                @endcan

                @can('manage-teachers')
                    <a href="{{ route('hod.teachers.index') }}" class="rounded-lg border border-ue-burgundy px-4 py-2.5 text-sm font-semibold text-ue-burgundy hover:bg-ue-burgundy hover:text-white">
                        {{ __('teachers.index.title') }}
                    </a>
                @endcan

                @can('viewAny', App\Models\User::class)
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-ue-burgundy px-4 py-2.5 text-sm font-semibold text-ue-burgundy hover:bg-ue-burgundy hover:text-white">
                        {{ __('users.index.title') }}
                    </a>
                @endcan

                <a href="{{ route('courses.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:border-ue-burgundy hover:text-ue-burgundy">
                    {{ __('app.courses') }}
                </a>
            </div>
        </section>
    </div>
@endsection
