<?php

namespace App\Events;

use App\Models\Goal;
use Illuminate\Foundation\Events\Dispatchable;

class GoalCreated
{
    use Dispatchable;

    public function __construct(public readonly Goal $goal) {}
}
