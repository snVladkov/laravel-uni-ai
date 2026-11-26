@extends('layouts.app')

@section('title', __('courses.create.title'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <header>
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-slate-500 hover:text-ue-burgundy">← {{ __('common.back') }}</a>
            <h1 class="mt-2 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ __('courses.create.title') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('courses.create.subtitle') }}</p>
        </header>

        <div class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm">
            @include('courses._form', ['course' => null, 'department' => $department])
        </div>
    </div>
@endsection
