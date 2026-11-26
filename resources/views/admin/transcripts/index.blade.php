@extends('layouts.app')

@section('title', __('transcripts.index.title'))

@section('content')
    <div class="space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-ue-crimson">{{ __('app.transcripts') }}</p>
                <h1 class="mt-1 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ __('transcripts.index.title') }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('transcripts.index.subtitle') }}</p>
            </div>
        </header>

        <div class="rounded-xl border border-ue-sand bg-white p-4 shadow-sm">
            <label for="student_selector" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('transcripts.index.select_student') }}
            </label>
            <select id="student_selector"
                    onchange="if (this.value) { window.location = this.value; }"
                    class="mt-1.5 block w-full max-w-md rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20">
                @foreach ($students as $availableStudent)
                    <option value="{{ route('admin.transcripts.index', $availableStudent) }}"
                            @selected($availableStudent->id === $student->id)>
                        {{ $availableStudent->name }} @if ($availableStudent->student_code) ({{ $availableStudent->student_code }}) @endif
                    </option>
                @endforeach
            </select>
        </div>

        <section class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="font-display text-2xl text-ue-burgundy">{{ $student->name }}</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ __('transcripts.show.student_code') }}: {{ $student->student_code ?: '—' }}
                        · {{ $student->email }}
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.transcripts.store', $student) }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-ue-burgundy-dark">
                        {{ __('transcripts.index.generate') }}
                    </button>
                </form>
            </div>

            @if ($student->enrollments()->whereNotNull('grade')->doesntExist())
                <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ __('transcripts.index.no_grades_hint') }}
                </p>
            @endif

            <h3 class="mt-8 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('transcripts.index.existing') }}</h3>

            @if ($transcripts->isEmpty())
                <p class="mt-3 text-sm text-slate-600">{{ __('transcripts.index.empty') }}</p>
            @else
                <ul class="mt-3 divide-y divide-ue-sand">
                    @foreach ($transcripts as $transcript)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                            <div>
                                <a href="{{ route('admin.transcripts.show', $transcript) }}"
                                   class="font-semibold text-ue-burgundy hover:underline">{{ $transcript->transcript_number }}</a>
                                <p class="text-xs text-slate-500">
                                    {{ __('transcripts.show.issued_on') }}: {{ $transcript->generated_at?->format('d.m.Y') }}
                                </p>
                            </div>

                            <div class="text-sm text-slate-600">
                                {{ __('transcripts.show.average') }}:
                                <span class="font-semibold text-ue-slate">
                                    {{ $transcript->gpa !== null ? number_format((float) $transcript->gpa, 2) : '—' }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
