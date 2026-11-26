@props(['course' => null, 'department' => null])

<form method="POST" action="{{ $course?->exists ? route('courses.update', $course) : route('courses.store') }}" class="space-y-6">
    @csrf
    @if ($course?->exists)
        @method('PUT')
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="block text-sm font-medium text-ue-slate">{{ __('common.title') }}</label>
            <input id="title" name="title" type="text" required maxlength="255"
                   value="{{ old('title', $course?->title) }}"
                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('title') border-red-400 @enderror">
            @error('title')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="code" class="block text-sm font-medium text-ue-slate">{{ __('common.code') }}</label>
            <input id="code" name="code" type="text" required maxlength="50" placeholder="ECO101"
                   value="{{ old('code', $course?->code) }}"
                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm uppercase shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('code') border-red-400 @enderror">
            @error('code')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-ue-slate">{{ __('common.department') }}</label>
            <p class="mt-2 rounded-lg border border-ue-sand bg-ue-cream/60 px-3 py-2.5 text-sm text-slate-600">
                {{ $department ?: __('common.none') }}
            </p>
            <p class="mt-1 text-xs text-slate-500">{{ __('courses.form.department_hint') }}</p>
        </div>

        <div class="sm:col-span-2">
            <label for="description" class="block text-sm font-medium text-ue-slate">{{ __('common.description') }}</label>
            <textarea id="description" name="description" rows="6" maxlength="5000"
                      class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-ue-burgundy focus:ring-2 focus:ring-ue-burgundy/20 @error('description') border-red-400 @enderror">{{ old('description', $course?->description) }}</textarea>
            @error('description')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 border-t border-ue-sand pt-6">
        <button type="submit" name="action" value="save"
                class="rounded-lg bg-ue-burgundy px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-ue-burgundy-dark focus:outline-none focus:ring-2 focus:ring-ue-burgundy focus:ring-offset-2">
            {{ $course?->exists ? __('common.save') : __('common.save_draft') }}
        </button>

        @unless ($course?->exists)
            <button type="submit" name="action" value="submit"
                    class="rounded-lg border border-ue-burgundy px-4 py-2.5 text-sm font-semibold text-ue-burgundy transition hover:bg-ue-burgundy hover:text-white focus:outline-none focus:ring-2 focus:ring-ue-burgundy focus:ring-offset-2">
                {{ __('common.submit_for_approval') }}
            </button>
        @endunless

        <a href="{{ $course?->exists ? route('courses.show', $course) : route('courses.index') }}"
           class="text-sm font-medium text-slate-600 hover:text-ue-burgundy">{{ __('common.cancel') }}</a>
    </div>
</form>
