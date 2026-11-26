@extends('layouts.app')

@section('title', __('courses.pending.title'))

@section('content')
    <div class="space-y-6">
        <header>
            <p class="text-sm font-medium uppercase tracking-wide text-ue-crimson">{{ __('app.pending_approvals') }}</p>
            <h1 class="mt-1 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ __('courses.pending.title') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('courses.pending.subtitle') }}</p>
        </header>

        @if ($courses->isEmpty())
            <div class="rounded-xl border border-dashed border-ue-tan/70 bg-white/70 p-12 text-center">
                <p class="font-display text-xl text-ue-burgundy">{{ __('courses.pending.empty') }}</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($courses as $course)
                    <article class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-ue-crimson">{{ $course->code }}</p>
                                <h2 class="mt-1 font-display text-xl text-ue-burgundy">
                                    <a href="{{ route('courses.show', $course) }}" class="hover:underline">{{ $course->title }}</a>
                                </h2>
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ __('courses.fields.teacher') }}: {{ $course->teacher?->name }} · {{ $course->department }}
                                </p>
                            </div>
                            <x-status-badge :status="$course->status" />
                        </div>

                        <p class="mt-4 line-clamp-3 text-sm text-slate-600">{{ $course->description }}</p>

                        <div class="mt-5 flex flex-wrap gap-3 border-t border-ue-sand pt-5">
                            <form method="POST" action="{{ route('hod.courses.approve', $course) }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                    {{ __('common.approve') }}
                                </button>
                            </form>

                            <details class="w-full rounded-lg border border-red-200 bg-red-50 p-4 sm:w-auto sm:min-w-96">
                                <summary class="cursor-pointer text-sm font-semibold text-red-800">{{ __('common.reject') }}</summary>
                                <form method="POST" action="{{ route('hod.courses.reject', $course) }}" class="mt-3 space-y-3">
                                    @csrf
                                    <label for="rejection_reason_{{ $course->id }}" class="block text-xs font-medium text-red-900">
                                        {{ __('courses.show.rejection_title') }} — {{ __('courses.pending.reject_hint') }}
                                    </label>
                                    <textarea id="rejection_reason_{{ $course->id }}" name="rejection_reason" rows="3" required minlength="5" maxlength="2000"
                                              class="block w-full rounded-lg border border-red-300 bg-white px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200">{{ old('rejection_reason') }}</textarea>
                                    @error('rejection_reason')
                                        <p class="text-sm text-red-700">{{ $message }}</p>
                                    @enderror
                                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                        {{ __('courses.pending.reject_confirm') }}
                                    </button>
                                </form>
                            </details>
                        </div>
                    </article>
                @endforeach
            </div>

            <div>{{ $courses->links() }}</div>
        @endif
    </div>
@endsection
