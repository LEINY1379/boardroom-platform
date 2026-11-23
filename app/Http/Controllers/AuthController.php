<?php

namespace App\Http\Controllers;

use App\Actions\Teams\EnsureCurrentTeam;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request, EnsureCurrentTeam $ensureCurrentTeam): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (! ($user instanceof User)) {
                abort(403);
            }

            $currentTeam = $ensureCurrentTeam->handle($user);

            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard', [
                    'current_team' => $currentTeam->slug,
                ]);
            }

            return redirect()->route('staff.dashboard', [
                'current_team' => $currentTeam->slug,
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}