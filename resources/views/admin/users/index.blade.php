@extends('layouts.app')

@section('title', __('users.index.title'))

@section('content')
    <div class="space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-ue-crimson">{{ __('app.users') }}</p>
                <h1 class="mt-1 font-display text-3xl uppercase tracking-wide text-ue-burgundy">{{ __('users.index.title') }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('users.index.subtitle') }}</p>
            </div>

            <a href="{{ route('admin.users.create') }}"
               class="rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-ue-burgundy-dark">
                {{ __('users.index.new') }}
            </a>
        </header>

        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-3 rounded-xl border border-ue-sand bg-white p-4 shadow-sm">
            <div class="min-w-56 flex-1">
                <label for="q" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('common.search') }}</label>
                <input id="q" name="q" type="search" value="{{ $search }}" placeholder="{{ __('users.index.search_placeholder') }}"
                       class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20">
            </div>

            <div>
                <label for="role" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('users.index.role_filter') }}</label>
                <select id="role" name="role"
                        class="mt-1.5 rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20">
                    <option value="">{{ __('users.index.all_roles') }}</option>
                    @foreach ($roles as $availableRole)
                        <option value="{{ $availableRole->name }}" @selected($role === $availableRole->name)>
                            {{ __('roles.'.$availableRole->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="rounded-lg border border-ue-burgundy px-4 py-2 text-sm font-semibold text-ue-burgundy transition hover:bg-ue-burgundy hover:text-white">
                {{ __('common.filter') }}
            </button>

            @if ($search !== '' || $role !== '')
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-500 hover:text-ue-burgundy">{{ __('common.reset_filters') }}</a>
            @endif
        </form>

        @if ($users->isEmpty())
            <div class="rounded-xl border border-dashed border-ue-tan/70 bg-white/70 p-12 text-center">
                <p class="font-display text-xl text-ue-burgundy">{{ __('users.index.empty') }}</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($users as $user)
                    <article class="rounded-xl border border-ue-sand bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-display text-lg text-ue-burgundy">{{ $user->name }}</p>
                                <p class="text-sm text-slate-600">{{ $user->email }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $user->department ?: __('common.none') }}
                                    @if ($user->teacher_code)
                                        · {{ __('users.fields.teacher_code') }}: {{ $user->teacher_code }}
                                    @endif
                                    @if ($user->student_code)
                                        · {{ __('users.fields.student_code') }}: {{ $user->student_code }}
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-col items-end gap-2">
                                <div class="flex flex-wrap justify-end gap-1.5">
                                    @foreach ($user->roles as $userRole)
                                        <span class="rounded-full bg-ue-sand/70 px-2.5 py-1 text-xs font-semibold text-ue-slate">
                                            {{ __('roles.'.$userRole->name) }}
                                        </span>
                                    @endforeach
                                </div>

                                @can('create', [App\Models\AcademicTranscript::class, $user])
                                    <a href="{{ route('admin.transcripts.index', $user) }}"
                                       class="text-xs font-semibold text-ue-burgundy hover:underline">
                                        {{ __('users.index.transcripts') }}
                                    </a>
                                @endcan
                            </div>
                        </div>

                        @can('syncRoles', $user)
                            <form method="POST" action="{{ route('admin.users.roles', $user) }}"
                                  class="mt-4 flex flex-wrap items-end gap-4 border-t border-ue-sand pt-4">
                                @csrf
                                @method('PATCH')

                                <fieldset class="flex flex-wrap items-center gap-3">
                                    <legend class="sr-only">{{ __('users.fields.roles') }}</legend>
                                    @foreach ($roles as $availableRole)
                                        <label class="inline-flex items-center gap-2 text-sm text-ue-slate">
                                            <input type="checkbox" name="roles[]" value="{{ $availableRole->name }}"
                                                   @checked($user->hasRole($availableRole->name))
                                                   class="size-4 rounded border-slate-300 text-ue-burgundy focus:ring-ue-burgundy">
                                            {{ __('roles.'.$availableRole->name) }}
                                        </label>
                                    @endforeach
                                </fieldset>

                                <div>
                                    <label for="department_{{ $user->id }}" class="block text-xs font-medium text-slate-600">
                                        {{ __('users.fields.department') }}
                                    </label>
                                    <input id="department_{{ $user->id }}" name="department" type="text" maxlength="120"
                                           value="{{ old('department', $user->department) }}"
                                           class="mt-1 w-56 rounded-lg border border-slate-300 px-3 py-1.5 text-sm shadow-sm outline-none focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20">
                                </div>

                                <button type="submit" class="rounded-lg bg-ue-burgundy px-3 py-2 text-xs font-semibold text-white hover:bg-ue-burgundy-dark">
                                    {{ __('users.index.sync_roles') }}
                                </button>
                            </form>
                        @endcan
                    </article>
                @endforeach
            </div>

            <div>{{ $users->links() }}</div>
        @endif
    </div>
@endsection
