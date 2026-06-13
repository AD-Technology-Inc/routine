<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserCapacityProfile;
use Illuminate\Support\Carbon;

class CapacityService
{
    public function getProfileForUser(User $user): UserCapacityProfile
    {
        return UserCapacityProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['daily_available_minutes' => 240]
        );
    }

    /**
     * @param array{daily_available_minutes?: int, preferred_time_blocks?: array<string>, monday_minutes?: int|null, tuesday_minutes?: int|null, wednesday_minutes?: int|null, thursday_minutes?: int|null, friday_minutes?: int|null, saturday_minutes?: int|null, sunday_minutes?: int|null} $data
     */
    public function updateProfile(User $user, array $data): UserCapacityProfile
    {
        $profile = $this->getProfileForUser($user);
        $profile->update($data);

        return $profile->fresh();
    }

    public function getAvailableMinutesForDate(User $user, Carbon $date): int
    {
        $profile = $this->getProfileForUser($user);

        return $profile->minutesForDate($date);
    }
}
