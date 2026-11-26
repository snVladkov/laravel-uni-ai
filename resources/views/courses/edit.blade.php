@extends('layouts.app')

@section('title', __('courses.edit.title'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <header>
            <a href="{{ route('courses.show', $course) }}" class="text-sm font-medium text-slate-500 hover:text-ue-burgundy">← {{ __('common.back') }}</a>
            <h1 class="mt-2 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ __('courses.edit.title') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('courses.edit.subtitle') }}</p>
        </header>

        @if ($course->status === \App\Enums\CourseStatus::Rejected)
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-semibold">{{ __('courses.show.rejection_title') }}</p>
                <p class="mt-1">{{ $course->rejection_reason }}</p>
            </div>
        @endif

        <div class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm">
            @include('courses._form', ['course' => $course, 'department' => $department])
        </div>
    </div>
@endsection
