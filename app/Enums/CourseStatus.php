<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';

    public function label(): string
    {
        return __('courses.statuses.'.$this->value);
    }

    public function colorClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700 ring-slate-500/20',
            self::PendingApproval => 'bg-amber-100 text-amber-800 ring-amber-600/20',
            self::Approved => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            self::Rejected => 'bg-red-100 text-red-800 ring-red-600/20',
            self::Completed => 'bg-ue-burgundy/10 text-ue-burgundy ring-ue-burgundy/20',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Rejected], true);
    }

    public function isVisibleToStudents(): bool
    {
        return in_array($this, [self::Approved, self::Completed], true);
    }

    public function isApprovedOrCompleted(): bool
    {
        return $this->isVisibleToStudents();
    }
}
