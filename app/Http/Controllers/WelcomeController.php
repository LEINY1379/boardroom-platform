<?php

namespace App\Http\Controllers;

use App\Actions\Teams\EnsureCurrentTeam;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function __invoke(Request $request, EnsureCurrentTeam $ensureCurrentTeam): View
    {
        $dashboardUrl = null;
        $user = $request->user();

        if ($user instanceof User) {
            $team = $ensureCurrentTeam->handle($user);
            $dashboardRoute = $user->role === 'admin' ? 'admin.dashboard' : 'staff.dashboard';
            $dashboardUrl = route($dashboardRoute, ['current_team' => $team->slug]);
        }

        return view('welcome', ['dashboardUrl' => $dashboardUrl]);
    }
}
