@props(['status'])

@php
    $status = $status instanceof \App\Enums\CourseStatus
        ? $status
        : \App\Enums\CourseStatus::from($status);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$status->colorClasses()]) }}>
    {{ $status->label() }}
</span>
