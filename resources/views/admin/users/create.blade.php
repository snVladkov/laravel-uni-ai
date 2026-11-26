@extends('layouts.app')

@section('title', __('users.create.title'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <header>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-500 hover:text-ue-burgundy">← {{ __('common.back') }}</a>
            <h1 class="mt-2 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ __('users.create.title') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('users.create.subtitle') }}</p>
        </header>

        <div class="rounded-xl border border-ue-sand bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                @csrf

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-ue-slate">{{ __('common.name') }}</label>
                        <input id="name" name="name" type="text" required maxlength="255" value="{{ old('name') }}"
                               class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('name') border-red-400 @enderror">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-ue-slate">{{ __('common.email') }}</label>
                        <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email') }}"
                               class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('email') border-red-400 @enderror">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-ue-slate">{{ __('common.password') }}</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                               class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('password') border-red-400 @enderror">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-ue-slate">{{ __('common.password_confirmation') }}</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                               class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20">
                    </div>

                    <div class="sm:col-span-2">
                        <fieldset>
                            <legend class="block text-sm font-medium text-ue-slate">{{ __('users.fields.roles') }}</legend>
                            <div class="mt-3 flex flex-wrap gap-4">
                                @foreach ($roles as $availableRole)
                                    <label class="inline-flex items-center gap-2 text-sm text-ue-slate">
                                        <input type="checkbox" name="roles[]" value="{{ $availableRole->name }}"
                                               @checked(in_array($availableRole->name, old('roles', []), true))
                                               class="size-4 rounded border-slate-300 text-ue-burgundy focus:ring-ue-burgundy">
                                        {{ __('roles.'.$availableRole->name) }}
                                    </label>
                                @endforeach
                            </div>
                            @error('roles')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </fieldset>
                    </div>

                    <div>
                        <label for="department" class="block text-sm font-medium text-ue-slate">{{ __('users.fields.department') }}</label>
                        <input id="department" name="department" type="text" maxlength="120" value="{{ old('department') }}"
                               class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('department') border-red-400 @enderror">
                        <p class="mt-1 text-xs text-slate-500">{{ __('users.fields.department_hint') }}</p>
                        @error('department')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="teacher_code" class="block text-sm font-medium text-ue-slate">{{ __('users.fields.teacher_code') }}</label>
                        <input id="teacher_code" name="teacher_code" type="text" maxlength="50" value="{{ old('teacher_code') }}"
                               class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('teacher_code') border-red-400 @enderror">
                        @error('teacher_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="student_code" class="block text-sm font-medium text-ue-slate">{{ __('users.fields.student_code') }}</label>
                        <input id="student_code" name="student_code" type="text" maxlength="50" value="{{ old('student_code') }}"
                               class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('student_code') border-red-400 @enderror">
                        @error('student_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 border-t border-ue-sand pt-6">
                    <button type="submit"
                            class="rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-ue-burgundy-dark">
                        {{ __('common.create') }}
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-600 hover:text-ue-burgundy">
                        {{ __('common.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
