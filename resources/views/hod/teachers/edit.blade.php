@extends('layouts.app')

@section('title', __('teachers.edit.title'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <header>
            <a href="{{ route('hod.teachers.index') }}" class="text-sm font-medium text-slate-500 hover:text-ue-burgundy">← {{ __('common.back') }}</a>
            <h1 class="mt-2 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ __('teachers.edit.title') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('teachers.edit.subtitle') }}</p>
        </header>

        <div class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm">
            @include('hod.teachers._form', ['teacher' => $teacher, 'department' => $department])
        </div>

        @can('manage-teacher', $teacher)
            <section class="rounded-xl border border-red-200 bg-red-50 p-6">
                <h2 class="font-display text-lg uppercase tracking-wide text-red-800">{{ __('teachers.delete.title') }}</h2>
                <p class="mt-2 text-sm text-red-700">{{ __('teachers.delete.warning') }}</p>

                <form method="POST" action="{{ route('hod.teachers.destroy', $teacher) }}" class="mt-4"
                      onsubmit="return confirm('{{ __('teachers.delete.confirm') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        {{ __('common.delete') }}
                    </button>
                </form>
            </section>
        @endcan
    </div>
@endsection
