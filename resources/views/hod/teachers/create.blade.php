@extends('layouts.app')

@section('title', __('teachers.create.title'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <header>
            <a href="{{ route('hod.teachers.index') }}" class="text-sm font-medium text-slate-500 hover:text-ue-burgundy">← {{ __('common.back') }}</a>
            <h1 class="mt-2 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ __('teachers.create.title') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('teachers.create.subtitle') }}</p>
        </header>

        <div class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm">
            @include('hod.teachers._form', ['teacher' => null, 'department' => $department])
        </div>
    </div>
@endsection
