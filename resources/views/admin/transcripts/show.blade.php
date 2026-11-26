@extends('layouts.app')

@section('title', __('transcripts.show.title').' — '.$transcript->transcript_number)

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="no-print flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.transcripts.index', $transcript->student) }}"
               class="text-sm font-medium text-slate-500 hover:text-ue-burgundy">← {{ __('transcripts.show.back') }}</a>

            <button type="button" onclick="window.print()"
                    class="rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-ue-burgundy-dark">
                {{ __('transcripts.print') }}
            </button>
        </div>

        <article class="rounded-xl border border-ue-sand bg-white p-8 shadow-sm print:border-0 print:shadow-none">
            <header class="flex flex-wrap items-start justify-between gap-6 border-b-2 border-ue-burgundy pb-6">
                <div class="flex items-center gap-4">
                    <span class="grid size-16 place-items-center rounded-full border-4 border-ue-gold bg-ue-burgundy font-display text-xl font-semibold text-ue-gold">
                        ИУ
                    </span>
                    <div>
                        <p class="font-display text-2xl uppercase tracking-wide text-ue-burgundy">{{ __('transcripts.show.university') }}</p>
                        <p class="text-sm text-slate-600">{{ __('app.name') }}</p>
                    </div>
                </div>

                <div class="text-right text-sm text-slate-600">
                    <p class="font-semibold uppercase tracking-wide text-ue-crimson">{{ __('transcripts.show.title') }}</p>
                    <p>{{ __('transcripts.show.number') }}: <span class="font-semibold text-ue-slate">{{ $transcript->transcript_number }}</span></p>
                    <p>{{ __('transcripts.show.issued_on') }}: {{ $transcript->generated_at?->format('d.m.Y') }}</p>
                </div>
            </header>

            <section class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg bg-ue-cream/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('transcripts.show.student') }}</p>
                    <p class="mt-1 font-display text-xl text-ue-burgundy">{{ $transcript->snapshot_data['student']['name'] ?? $transcript->student?->name }}</p>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ __('transcripts.show.student_code') }}: {{ $transcript->snapshot_data['student']['student_code'] ?? $transcript->student?->student_code ?: '—' }}
                    </p>
                    <p class="text-sm text-slate-600">{{ $transcript->snapshot_data['student']['email'] ?? $transcript->student?->email }}</p>
                </div>

                <div class="rounded-lg bg-ue-cream/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('transcripts.show.average') }}</p>
                    <p class="mt-1 font-display text-4xl text-ue-burgundy">
                        {{ $transcript->gpa !== null ? number_format((float) $transcript->gpa, 2) : '—' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('transcripts.show.scale_note') }}</p>
                </div>
            </section>

            <section class="mt-8">
                <h2 class="font-display text-lg uppercase tracking-wide text-ue-burgundy">{{ __('transcripts.show.courses') }}</h2>

                @if (empty($transcript->snapshot_data['courses']))
                    <p class="mt-3 text-sm text-slate-600">{{ __('transcripts.show.no_courses') }}</p>
                @else
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-ue-sand text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th class="py-2 pr-4 font-semibold">{{ __('transcripts.show.course') }}</th>
                                    <th class="py-2 pr-4 font-semibold">{{ __('transcripts.show.department') }}</th>
                                    <th class="py-2 pr-4 font-semibold">{{ __('transcripts.show.grade') }}</th>
                                    <th class="py-2 font-semibold">{{ __('transcripts.show.letter') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ue-sand">
                                @foreach ($transcript->snapshot_data['courses'] as $course)
                                    <tr>
                                        <td class="py-2.5 pr-4">
                                            <span class="font-medium text-ue-slate">{{ $course['title'] }}</span>
                                            <span class="ml-1 text-xs text-slate-500">{{ $course['code'] }}</span>
                                        </td>
                                        <td class="py-2.5 pr-4 text-slate-600">{{ $course['department'] ?: '—' }}</td>
                                        <td class="py-2.5 pr-4 font-semibold text-ue-burgundy">{{ number_format((float) $course['grade'], 2) }}</td>
                                        <td class="py-2.5 text-slate-600">
                                            {{ \App\Enums\GradeBand::fromLetter($course['grade_letter'])?->label() }}
                                            ({{ $course['grade_letter'] }})
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <footer class="mt-10 flex flex-wrap items-end justify-between gap-6 border-t border-ue-sand pt-6 text-sm text-slate-600">
                <div>
                    <p>{{ __('transcripts.show.issued_by') }}: {{ $transcript->generator?->name }}</p>
                    <p class="text-xs text-slate-500">{{ $transcript->snapshot_data['generated_at'] ?? '' }}</p>
                </div>

                <div class="w-64 border-t border-slate-400 pt-2 text-center text-xs text-slate-500">
                    {{ __('transcripts.show.signature') }}
                </div>
            </footer>
        </article>
    </div>
@endsection
