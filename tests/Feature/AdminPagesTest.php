<?php

use App\Models\Boardroom;
use App\Models\Meeting;
use App\Models\User;

test('admin can access the new boardroom, user, and booking pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $team = $admin->currentTeam;
    Boardroom::factory()->create(['status' => 'Available']);

    $this->actingAs($admin)
        ->get(route('admin.boardrooms.create', ['current_team' => $team->slug]))
        ->assertOk()
        ->assertViewIs('admin.boardrooms.create')
        ->assertSee(route('admin.boardrooms.store', ['current_team' => $team->slug]), false);

    $this->get(route('admin.users.create', ['current_team' => $team->slug]))
        ->assertOk()
        ->assertViewIs('admin.users.create')
        ->assertSee(route('admin.users.store', ['current_team' => $team->slug]), false);

    $this->get(route('admin.bookings.create', ['current_team' => $team->slug]))
        ->assertOk()
        ->assertViewIs('admin.bookings.create')
        ->assertSee(route('admin.bookings.store', ['current_team' => $team->slug]), false);
});

test('admin can create boardrooms and bookings from the new pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $team = $admin->currentTeam;

    $this->actingAs($admin)
        ->from(route('admin.boardrooms.create', ['current_team' => $team->slug]))
        ->post(route('admin.boardrooms.store', ['current_team' => $team->slug]), [
            'name' => 'Executive North',
            'capacity' => 14,
            'location' => 'North Wing',
            'amenities' => 'Video conferencing',
            'status' => 'Available',
        ])
        ->assertRedirect(route('admin.boardrooms.create', ['current_team' => $team->slug]));

    $boardroom = Boardroom::where('name', 'Executive North')->firstOrFail();

    $this->from(route('admin.bookings.create', ['current_team' => $team->slug]))
        ->post(route('admin.bookings.store', ['current_team' => $team->slug]), [
            'title' => 'Admin Planning Session',
            'boardroom_id' => $boardroom->id,
            'meeting_date' => now()->addDays(3)->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'agenda' => 'Review quarterly plans.',
        ])
        ->assertRedirect(route('admin.bookings.create', ['current_team' => $team->slug]))
        ->assertSessionHas('success');

    expect(Meeting::where('title', 'Admin Planning Session')
        ->where('created_by', $admin->id)
        ->exists())->toBeTrue();
});

test('staff cannot access admin creation pages', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get(route('admin.boardrooms.create', ['current_team' => $staff->currentTeam->slug]))
        ->assertForbidden();
});
