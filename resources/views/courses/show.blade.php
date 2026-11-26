@extends('layouts.app')

@section('title', $course->title)

@section('content')
    <div class="space-y-6">
        <header class="space-y-3">
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-slate-500 hover:text-ue-burgundy">← {{ __('common.back') }}</a>

            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ue-crimson">{{ $course->code }} · {{ $course->department }}</p>
                    <h1 class="mt-1 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ $course->title }}</h1>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('courses.fields.teacher') }}: <span class="font-medium text-ue-slate">{{ $course->teacher?->name }}</span>
                    </p>
                </div>
                <x-status-badge :status="$course->status" class="text-sm" />
            </div>
        </header>

        @if ($course->status === \App\Enums\CourseStatus::Rejected)
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-semibold">{{ __('courses.show.rejection_title') }}</p>
                <p class="mt-1">{{ $course->rejection_reason }}</p>
                <p class="mt-2 text-red-700/80">{{ __('courses.show.rejection_hint') }}</p>
            </div>
        @endif

        @if ($course->status === \App\Enums\CourseStatus::Draft)
            @can('submitForApproval', $course)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <p>{{ __('courses.show.submit_hint') }}</p>
                    <form method="POST" action="{{ route('courses.submit', $course) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                            {{ __('common.submit_for_approval') }}
                        </button>
                    </form>
                </div>
            @endcan
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="font-display text-xl uppercase tracking-wide text-ue-burgundy">{{ __('courses.show.overview') }}</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $course->description ?: __('common.none') }}</p>

                <div class="mt-6 flex flex-wrap gap-2 border-t border-ue-sand pt-6">
                    @can('update', $course)
                        <a href="{{ route('courses.edit', $course) }}"
                           class="rounded-lg border border-ue-burgundy px-4 py-2 text-sm font-semibold text-ue-burgundy hover:bg-ue-burgundy hover:text-white">
                            {{ __('common.edit') }}
                        </a>
                    @endcan

                    @can('approve', $course)
                        <form method="POST" action="{{ route('hod.courses.approve', $course) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                {{ __('common.approve') }}
                            </button>
                        </form>
                    @endcan

                    @can('markCompleted', $course)
                        <form method="POST" action="{{ route('hod.courses.complete', $course) }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-ue-burgundy px-4 py-2 text-sm font-semibold text-white hover:bg-ue-burgundy-dark">
                                {{ __('common.complete') }}
                            </button>
                        </form>
                    @endcan
                </div>

                @can('reject', $course)
                    <details class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-red-800">{{ __('common.reject') }}</summary>
                        <form method="POST" action="{{ route('hod.courses.reject', $course) }}" class="mt-3 space-y-3">
                            @csrf
                            <label for="rejection_reason" class="block text-sm font-medium text-red-900">{{ __('courses.fields.rejection_reason') }}</label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="3" required minlength="5" maxlength="2000"
                                      class="block w-full rounded-lg border border-red-300 bg-white px-3 py-2 text-sm outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200">{{ old('rejection_reason') }}</textarea>
                            @error('rejection_reason')
                                <p class="text-sm text-red-700">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                {{ __('courses.pending.reject_confirm') }}
                            </button>
                        </form>
                    </details>
                @endcan
            </section>

            <aside class="space-y-4">
                <div class="rounded-xl border border-ue-sand bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('courses.fields.department') }}</h2>
                    <p class="mt-2 font-display text-lg text-ue-burgundy">{{ $course->department ?: __('common.none') }}</p>
                </div>

                @if ($ownEnrollment)
                    <div class="rounded-xl border border-ue-gold/40 bg-white p-5 shadow-sm">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('courses.show.your_grade') }}</h2>
                        @if ($ownEnrollment->grade !== null)
                            <p class="mt-3 font-display text-4xl text-ue-burgundy">{{ number_format((float) $ownEnrollment->grade, 2) }}</p>
                            @if ($ownEnrollment->grade_letter)
                                <p class="mt-1 text-sm font-medium text-slate-600">
                                    {{ \App\Enums\GradeBand::fromLetter($ownEnrollment->grade_letter)?->label() }}
                                    ({{ $ownEnrollment->grade_letter }})
                                </p>
                            @endif
                            @if ($ownEnrollment->graded_at)
                                <p class="mt-2 text-xs text-slate-500">{{ __('courses.show.graded_on') }} {{ $ownEnrollment->graded_at->format('d.m.Y') }}</p>
                            @endif
                        @else
                            <p class="mt-3 text-sm text-slate-600">{{ __('courses.show.not_graded') }}</p>
                        @endif
                    </div>
                @endif
            </aside>
        </div>

        <section class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm">
            <h2 class="font-display text-xl uppercase tracking-wide text-ue-burgundy">{{ __('courses.show.materials') }}</h2>

            @if ($course->materials->isEmpty())
                <p class="mt-3 text-sm text-slate-600">{{ __('courses.show.no_materials') }}</p>
            @else
                <ul class="mt-4 divide-y divide-ue-sand">
                    @foreach ($course->materials as $material)
                        <li class="flex flex-wrap items-start justify-between gap-3 py-4">
                            <div>
                                <p class="font-medium text-ue-slate">{{ $material->title }}</p>
                                @if ($material->description)
                                    <p class="mt-1 text-sm text-slate-600">{{ $material->description }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ __('courses.materials.uploaded_by') }}: {{ $material->uploader?->name }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3 text-sm">
                                @if ($material->external_url)
                                    <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer"
                                       class="font-semibold text-ue-crimson hover:underline">{{ __('common.external_link') }}</a>
                                @endif

                                @if ($material->file_path)
                                    @can('download', $material)
                                        <a href="{{ route('courses.materials.download', [$course, $material]) }}"
                                           class="font-semibold text-ue-burgundy hover:underline">{{ __('common.download') }}</a>
                                    @endcan
                                @endif

                                @can('delete', $material)
                                    <form method="POST" action="{{ route('courses.materials.destroy', [$course, $material]) }}"
                                          onsubmit="return confirm('{{ __('courses.materials.delete_confirm') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-red-600 hover:underline">
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            @can('create', [App\Models\CourseMaterial::class, $course])
                <details class="mt-6 rounded-lg border border-dashed border-ue-tan/70 bg-ue-cream/50 p-4">
                    <summary class="cursor-pointer text-sm font-semibold text-ue-burgundy">{{ __('courses.materials.add_title') }}</summary>

                    <form method="POST" action="{{ route('courses.materials.store', $course) }}" enctype="multipart/form-data"
                          class="mt-4 grid gap-4 sm:grid-cols-2">
                        @csrf

                        <div class="sm:col-span-2">
                            <label for="material_title" class="block text-sm font-medium text-ue-slate">{{ __('common.title') }}</label>
                            <input id="material_title" name="title" type="text" required maxlength="255" value="{{ old('title') }}"
                                   class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('title') border-red-400 @enderror">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="material_description" class="block text-sm font-medium text-ue-slate">{{ __('common.description') }}</label>
                            <textarea id="material_description" name="description" rows="2" maxlength="2000"
                                      class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label for="material_file" class="block text-sm font-medium text-ue-slate">{{ __('courses.materials.file') }}</label>
                            <input id="material_file" name="file" type="file"
                                   class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-ue-burgundy file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-ue-burgundy-dark @error('file') border-red-400 @enderror">
                            <p class="mt-1 text-xs text-slate-500">{{ __('courses.materials.file_hint') }}</p>
                            @error('file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="material_url" class="block text-sm font-medium text-ue-slate">{{ __('courses.materials.external_url') }}</label>
                            <input id="material_url" name="external_url" type="url" maxlength="2048" value="{{ old('external_url') }}"
                                   placeholder="https://"
                                   class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('external_url') border-red-400 @enderror">
                            @error('external_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <button type="submit"
                                    class="rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-ue-burgundy-dark">
                                {{ __('common.upload') }}
                            </button>
                        </div>
                    </form>
                </details>
            @endcan
        </section>

        @can('viewRoster', $course)
            <section class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-display text-xl uppercase tracking-wide text-ue-burgundy">{{ __('courses.show.students') }}</h2>
                    <span class="rounded-full bg-ue-sand/70 px-3 py-1 text-xs font-semibold text-ue-slate">
                        {{ trans_choice('courses.show.student_count', $course->enrollments->count(), ['count' => $course->enrollments->count()]) }}
                    </span>
                </div>

                @if ($course->enrollments->isEmpty())
                    <p class="mt-3 text-sm text-slate-600">{{ __('courses.show.no_students') }}</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-ue-sand text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th class="py-3 pr-4 font-semibold">{{ __('common.name') }}</th>
                                    <th class="py-3 pr-4 font-semibold">{{ __('common.student_code') }}</th>
                                    <th class="py-3 pr-4 font-semibold">{{ __('common.grade') }}</th>
                                    <th class="py-3 pr-4 font-semibold">{{ __('courses.show.enrolled_on') }}</th>
                                    @if (auth()->user()->can('grade', $course) || auth()->user()->can('removeStudent', $course))
                                        <th class="py-3 font-semibold">{{ __('common.actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ue-sand">
                                @foreach ($course->enrollments as $enrollment)
                                    <tr>
                                        <td class="py-3 pr-4 font-medium text-ue-slate">{{ $enrollment->student?->name }}</td>
                                        <td class="py-3 pr-4 text-slate-600">{{ $enrollment->student?->student_code }}</td>
                                        <td class="py-3 pr-4">
                                            @if ($enrollment->grade !== null)
                                                <span class="font-semibold text-ue-burgundy">{{ number_format((float) $enrollment->grade, 2) }}</span>
                                                @if ($enrollment->grade_letter)
                                                    <span class="ml-1 text-xs text-slate-500">
                                                        {{ \App\Enums\GradeBand::fromLetter($enrollment->grade_letter)?->label() }}
                                                        ({{ $enrollment->grade_letter }})
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-slate-500">{{ __('courses.show.not_graded') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 pr-4 text-slate-600">{{ $enrollment->enrolled_at?->format('d.m.Y') }}</td>

                                        @if (auth()->user()->can('grade', $course) || auth()->user()->can('removeStudent', $course))
                                            <td class="py-3">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    @can('grade', $course)
                                                        <form method="POST"
                                                              action="{{ route('courses.students.grade', [$course, $enrollment->student_id]) }}"
                                                              class="flex items-center gap-2">
                                                            @csrf
                                                            @method('PATCH')
                                                            <label class="sr-only" for="grade_{{ $enrollment->id }}">{{ __('common.grade') }}</label>
                                                            <input id="grade_{{ $enrollment->id }}" name="grade" type="number" step="0.5" min="2" max="6"
                                                                   value="{{ $enrollment->grade !== null ? number_format((float) $enrollment->grade, 2) : '' }}"
                                                                   placeholder="2.00–6.00"
                                                                   class="w-24 rounded-lg border border-slate-300 px-2 py-1.5 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20">
                                                            <button type="submit" class="rounded-lg bg-ue-burgundy px-3 py-1.5 text-xs font-semibold text-white hover:bg-ue-burgundy-dark">
                                                                {{ __('common.save') }}
                                                            </button>
                                                        </form>
                                                    @endcan

                                                    @can('removeStudent', $course)
                                                        <form method="POST"
                                                              action="{{ route('courses.students.destroy', [$course, $enrollment->student_id]) }}"
                                                              onsubmit="return confirm('{{ __('courses.show.remove_confirm') }}');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">
                                                                {{ __('common.remove') }}
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @can('enroll', $course)
                    <form method="POST" action="{{ route('courses.students.store', $course) }}"
                          class="mt-6 flex flex-wrap items-end gap-3 rounded-lg border border-dashed border-ue-tan/70 bg-ue-cream/50 p-4">
                        @csrf
                        <div class="min-w-64 flex-1">
                            <label for="student_id" class="block text-sm font-medium text-ue-slate">{{ __('courses.show.enroll_label') }}</label>
                            <select id="student_id" name="student_id" required
                                    class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('student_id') border-red-400 @enderror">
                                <option value="">{{ __('courses.show.enroll_placeholder') }}</option>
                                @foreach ($availableStudents as $student)
                                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                        {{ $student->name }} @if ($student->student_code) ({{ $student->student_code }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="rounded-lg bg-ue-burgundy px-4 py-2 text-sm font-semibold text-white hover:bg-ue-burgundy-dark">
                            {{ __('common.enroll') }}
                        </button>
                    </form>
                @endcan
            </section>
        @endcan
    </div>
@endsection
