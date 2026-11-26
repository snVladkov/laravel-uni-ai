@extends('layouts.guest')

@section('title', __('auth.login'))

@section('content')
    <h1 class="font-display text-2xl uppercase tracking-wide text-ue-burgundy">{{ __('auth.login') }}</h1>
    <p class="mt-2 text-sm text-slate-600">{{ __('auth.login_prompt') }}</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-ue-slate">{{ __('auth.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-ue-slate">{{ __('auth.password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-ue-burgundy focus:ring-ue-burgundy">
            {{ __('auth.remember_me') }}
        </label>

        <button type="submit"
                class="w-full rounded-lg bg-ue-burgundy px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-ue-burgundy-dark focus:outline-none focus:ring-2 focus:ring-ue-burgundy focus:ring-offset-2">
            {{ __('auth.sign_in') }}
        </button>
    </form>

    <div class="mt-8 rounded-lg border border-dashed border-ue-tan/60 bg-ue-cream/60 p-4 text-xs text-slate-600">
        <p class="font-semibold text-ue-burgundy">{{ __('auth.demo_title') }}</p>
        <p class="mt-1">{{ __('auth.demo_hint') }}</p>
    </div>
@endsection
