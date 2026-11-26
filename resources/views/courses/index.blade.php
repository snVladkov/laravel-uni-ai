@extends('layouts.app')

@section('title', __('courses.index.title'))

@section('content')
    <div class="space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-ue-crimson">{{ __('app.courses') }}</p>
                <h1 class="mt-1 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ __('courses.index.title') }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('courses.index.subtitle') }}</p>
            </div>

            @can('create', App\Models\Course::class)
                <a href="{{ route('courses.create') }}"
                   class="rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-ue-burgundy-dark focus:outline-none focus:ring-2 focus:ring-ue-burgundy focus:ring-offset-2">
                    {{ __('courses.index.new') }}
                </a>
            @endcan
        </header>

        <form method="GET" action="{{ route('courses.index') }}" class="flex flex-wrap items-end gap-3 rounded-xl border border-ue-sand bg-white p-4 shadow-sm">
            <div class="min-w-56 flex-1">
                <label for="q" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('common.search') }}</label>
                <input id="q" name="q" type="search" value="{{ $search }}"
                       placeholder="{{ __('courses.index.search_placeholder') }}"
                       class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20">
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('common.status') }}</label>
                <select id="status" name="status"
                        class="mt-1.5 rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach (\App\Enums\CourseStatus::cases() as $case)
                        <option value="{{ $case->value }}" @selected($status === $case->value)>{{ $case->label() }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="rounded-lg border border-ue-burgundy px-4 py-2 text-sm font-semibold text-ue-burgundy transition hover:bg-ue-burgundy hover:text-white">
                {{ __('common.filter') }}
            </button>

            @if ($search !== '' || $status !== '')
                <a href="{{ route('courses.index') }}" class="text-sm font-medium text-slate-500 hover:text-ue-burgundy">{{ __('common.reset_filters') }}</a>
            @endif
        </form>

        @if ($courses->isEmpty())
            <div class="rounded-xl border border-dashed border-ue-tan/70 bg-white/70 p-12 text-center">
                <p class="font-display text-xl text-ue-burgundy">{{ __('courses.index.empty') }}</p>
                @can('create', App\Models\Course::class)
                    <a href="{{ route('courses.create') }}" class="mt-4 inline-block rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white hover:bg-ue-burgundy-dark">
                        {{ __('courses.index.empty_cta') }}
                    </a>
                @endcan
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($courses as $course)
                    <article class="flex flex-col rounded-xl border border-ue-sand bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-ue-crimson">{{ $course->code }}</p>
                                <h2 class="mt-1 font-display text-xl leading-tight text-ue-burgundy">
                                    <a href="{{ route('courses.show', $course) }}" class="hover:underline">{{ $course->title }}</a>
                                </h2>
                            </div>
                            <x-status-badge :status="$course->status" />
                        </div>

                        <p class="mt-3 line-clamp-3 text-sm text-slate-600">{{ $course->description }}</p>

                        <dl class="mt-4 grid grid-cols-2 gap-2 text-xs text-slate-500">
                            <div>
                                <dt class="font-semibold text-slate-600">{{ __('courses.fields.teacher') }}</dt>
                                <dd class="mt-0.5">{{ $course->teacher?->name }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-slate-600">{{ __('courses.fields.department') }}</dt>
                                <dd class="mt-0.5">{{ $course->department }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('courses.show', $course) }}"
                           class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-ue-burgundy hover:gap-2">
                            {{ __('common.view') }} <span aria-hidden="true">→</span>
                        </a>
                    </article>
                @endforeach
            </div>

            <div>{{ $courses->links() }}</div>
        @endif
    </div>
@endsection
