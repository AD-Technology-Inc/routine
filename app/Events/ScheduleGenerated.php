<?php

namespace App\Events;

use App\Models\ScheduledSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;

class ScheduleGenerated
{
    use Dispatchable;

    /** @param Collection<int, ScheduledSlot> $slots */
    public function __construct(
        public readonly User $user,
        public readonly Collection $slots,
    ) {}
}
