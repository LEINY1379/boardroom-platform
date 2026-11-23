<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;

class EnsureCurrentTeam
{
    public function handle(User $user): Team
    {
        $team = $user->currentTeam;

        if ($team && $user->belongsToTeam($team)) {
            return $team;
        }

        $team = $user->personalTeam() ?? $user->fallbackTeam();

        if (! $team) {
            return app(CreateTeam::class)->handle($user, $user->name."'s Team", true);
        }

        $user->switchTeam($team);

        return $team;
    }
}
