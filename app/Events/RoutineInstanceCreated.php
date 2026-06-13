<?php

namespace App\Events;

use App\Models\RoutineInstance;
use Illuminate\Foundation\Events\Dispatchable;

class RoutineInstanceCreated
{
    use Dispatchable;

    public function __construct(public readonly RoutineInstance $instance) {}
}
