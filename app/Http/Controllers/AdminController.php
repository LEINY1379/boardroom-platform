<?php

namespace App\Http\Controllers;

use App\Enums\TeamRole;
use App\Models\Boardroom;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function createBoardroom(): View
    {
        return view('admin.boardrooms.create');
    }

    public function createUser(): View
    {
        return view('admin.users.create');
    }

    public function createBooking(): View
    {
        $boardrooms = Boardroom::where('status', 'Available')
            ->orderBy('name')
            ->get();

        return view('admin.bookings.create', compact('boardrooms'));
    }

    public function dashboard()
    {
        $boardrooms = Boardroom::orderBy('name')->get();
        $allMeetings = Meeting::with(['boardroom', 'creator'])
            ->orderByDesc('meeting_date')
            ->orderBy('start_time')
            ->get();
        $users = User::orderBy('name')->get();

        $activeBookingsCount = $allMeetings->where('status', 'Scheduled')->count();
        $totalUsersCount = User::count();

        // Simple utilization metric: percentage of rooms currently active/available vs booked today
        $todayBookings = $allMeetings
            ->where('meeting_date', date('Y-m-d'))
            ->where('status', 'Scheduled')
            ->count();
        $totalRooms = max($boardrooms->count(), 1);
        $utilizationRate = min(round(($todayBookings / ($totalRooms * 4)) * 100), 100).'%';

        return view('admin.dashboard', compact(
            'boardrooms',
            'allMeetings',
            'users',
            'activeBookingsCount',
            'totalUsersCount',
            'utilizationRate'
        ));
    }

    public function storeBoardroom(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:boardrooms,name'],
            'capacity' => ['required', 'integer', 'min:2', 'max:500'],
            'location' => ['required', 'string', 'max:100'],
            'amenities' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:Available,Maintenance'],
        ]);

        Boardroom::create($validated);

        return back()->with('success', 'New boardroom asset provisioned successfully.');
    }

    public function toggleBoardroomStatus($current_team, Boardroom $boardroom)
    {
        $boardroom->update([
            'status' => $boardroom->status === 'Available' ? 'Maintenance' : 'Available',
        ]);

        return back()->with('success', "Status updated for {$boardroom->name}.");
    }

    public function destroyBoardroom($current_team, Boardroom $boardroom)
    {
        $boardroom->delete();

        return back()->with('success', 'Boardroom asset removed from the platform.');
    }

    public function overrideMeeting($current_team, Meeting $meeting)
    {
        $meeting->update(['status' => 'Cancelled']);

        return back()->with('success', "Executive override applied: '{$meeting->title}' has been cancelled and the slot released.");
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
        ]);

        $admin = $request->user();
        abort_if(! $admin instanceof User, 403);

        $team = $admin->currentTeam;
        abort_if(! $team, 403, 'An active team is required to create a user account.');

        $newUser = DB::transaction(function () use ($validated, $team): User {
            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
                'current_team_id' => $team->id,
                'email_verified_at' => now(),
            ]);

            $newUser->teams()->syncWithoutDetaching([
                $team->id => ['role' => TeamRole::Member->value],
            ]);

            return $newUser;
        });

        return back()->with('success', "Account created for {$newUser->name} ({$newUser->role}).");
    }

    public function toggleUserRole($current_team, User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['role' => 'You cannot demote your own active administrator account.']);
        }

        $user->update([
            'role' => $user->role === 'admin' ? 'staff' : 'admin',
        ]);

        return back()->with('success', "Role updated: {$user->name} is now set to {$user->role}.");
    }

    public function destroyUser($current_team, User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'You cannot delete your own active administrator account.']);
        }

        $user->delete();

        return back()->with('success', "Institutional account for {$user->name} has been revoked.");
    }
}
