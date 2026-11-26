@extends('layouts.app')

@section('title', __('teachers.index.title'))

@section('content')
    <div class="space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-ue-crimson">{{ __('app.teachers') }}</p>
                <h1 class="mt-1 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ __('teachers.index.title') }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('teachers.index.subtitle') }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ __('teachers.index.department_scope', ['department' => auth()->user()->department]) }}</p>
            </div>

            <a href="{{ route('hod.teachers.create') }}"
               class="rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-ue-burgundy-dark">
                {{ __('teachers.index.new') }}
            </a>
        </header>

        @if ($teachers->isEmpty())
            <div class="rounded-xl border border-dashed border-ue-tan/70 bg-white/70 p-12 text-center">
                <p class="font-display text-xl text-ue-burgundy">{{ __('teachers.index.empty') }}</p>
                <a href="{{ route('hod.teachers.create') }}" class="mt-4 inline-block rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white hover:bg-ue-burgundy-dark">
                    {{ __('teachers.index.empty_cta') }}
                </a>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-ue-sand bg-white shadow-sm">
                <table class="min-w-full divide-y divide-ue-sand text-sm">
                    <thead class="bg-ue-cream/60">
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 font-semibold">{{ __('teachers.fields.name') }}</th>
                            <th class="px-4 py-3 font-semibold">{{ __('teachers.fields.email') }}</th>
                            <th class="px-4 py-3 font-semibold">{{ __('teachers.fields.teacher_code') }}</th>
                            <th class="px-4 py-3 font-semibold">{{ __('teachers.fields.department') }}</th>
                            <th class="px-4 py-3 text-right font-semibold">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ue-sand">
                        @foreach ($teachers as $teacher)
                            <tr>
                                <td class="px-4 py-3 font-medium text-ue-slate">{{ $teacher->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $teacher->email }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $teacher->teacher_code ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $teacher->department }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        @can('manage-teacher', $teacher)
                                            <a href="{{ route('hod.teachers.edit', $teacher) }}"
                                               class="text-sm font-semibold text-ue-burgundy hover:underline">{{ __('common.edit') }}</a>

                                            <form method="POST" action="{{ route('hod.teachers.destroy', $teacher) }}"
                                                  onsubmit="return confirm('{{ __('teachers.delete.confirm') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-semibold text-red-600 hover:underline">
                                                    {{ __('common.delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>{{ $teachers->links() }}</div>
        @endif
    </div>
@endsection
