@props(['teacher' => null, 'department' => null])

<form method="POST" action="{{ $teacher?->exists ? route('hod.teachers.update', $teacher) : route('hod.teachers.store') }}" class="space-y-6">
    @csrf
    @if ($teacher?->exists)
        @method('PUT')
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="block text-sm font-medium text-ue-slate">{{ __('teachers.fields.name') }}</label>
            <input id="name" name="name" type="text" required maxlength="255" value="{{ old('name', $teacher?->name) }}"
                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-ue-slate">{{ __('teachers.fields.email') }}</label>
            <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email', $teacher?->email) }}"
                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="teacher_code" class="block text-sm font-medium text-ue-slate">{{ __('teachers.fields.teacher_code') }}</label>
            <input id="teacher_code" name="teacher_code" type="text" maxlength="50" value="{{ old('teacher_code', $teacher?->teacher_code) }}"
                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('teacher_code') border-red-400 @enderror">
            @error('teacher_code')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-ue-slate">{{ __('teachers.fields.department') }}</label>
            <p class="mt-2 rounded-lg border border-ue-sand bg-ue-cream/60 px-3 py-2.5 text-sm text-slate-600">
                {{ $department ?: __('common.none') }}
            </p>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-ue-slate">
                {{ $teacher?->exists ? __('teachers.fields.password_optional') : __('teachers.fields.password') }}
            </label>
            <input id="password" name="password" type="password" autocomplete="new-password" @required(! $teacher?->exists)
                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-ue-slate">{{ __('teachers.fields.password_confirmation') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20">
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 border-t border-ue-sand pt-6">
        <button type="submit"
                class="rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-ue-burgundy-dark focus:outline-none focus:ring-2 focus:ring-ue-burgundy focus:ring-offset-2">
            {{ __('common.save') }}
        </button>
        <a href="{{ route('hod.teachers.index') }}" class="text-sm font-medium text-slate-600 hover:text-ue-burgundy">
            {{ __('common.cancel') }}
        </a>
    </div>
</form>
