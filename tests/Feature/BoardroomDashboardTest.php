<?php

use App\Models\Boardroom;
use App\Models\Meeting;
use App\Models\User;

test('staff dashboard displays boardrooms and the users meetings', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $boardroom = Boardroom::factory()->create([
        'name' => 'Executive Suite',
        'capacity' => 20,
        'location' => '3rd Floor',
    ]);
    $meeting = Meeting::factory()->create([
        'title' => 'Quarterly Planning',
        'boardroom_id' => $boardroom->id,
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('staff.dashboard', [
        'current_team' => $user->currentTeam->slug,
    ]));

    $response
        ->assertOk()
        ->assertSee('Executive Suite')
        ->assertSee(route('bookings.create', ['current_team' => $user->currentTeam]), false)
        ->assertSee('Create a Booking')
        ->assertSee('Daily Availability Grid')
        ->assertSee('name="date"', false)
        ->assertSee(route('bookings.create', [
            'current_team' => $user->currentTeam,
            'boardroom_id' => $boardroom->id,
            'meeting_date' => now()->format('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]))
        ->assertDontSee('prefillBookingSlot', false)
        ->assertDontSee('Reserve a Boardroom')
        ->assertSee('Quarterly Planning')
        ->assertSee('3rd Floor')
        ->assertSee(route('meetings.show', [
            'current_team' => $user->currentTeam,
            'meeting' => $meeting->id,
        ]), false);

    $this->get(route('meetings.show', [
        'current_team' => $user->currentTeam,
        'meeting' => $meeting->id,
    ]))
        ->assertOk()
        ->assertViewIs('meetings.show')
        ->assertSee('Governance Workspace')
        ->assertSee('Quarterly Planning')
        ->assertSee('Executive Suite');
});

test('staff dashboard provides non-cancelled meetings for the selected calendar date', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $boardroom = Boardroom::factory()->create();
    $calendarDate = now()->addDays(3)->format('Y-m-d');
    $meeting = Meeting::factory()->create([
        'boardroom_id' => $boardroom->id,
        'created_by' => $user->id,
        'meeting_date' => $calendarDate,
        'start_time' => '09:00:00',
    ]);
    $laterMeeting = Meeting::factory()->create([
        'boardroom_id' => $boardroom->id,
        'created_by' => $user->id,
        'meeting_date' => $calendarDate,
        'start_time' => '11:00:00',
    ]);
    $cancelledMeeting = Meeting::factory()->create([
        'boardroom_id' => $boardroom->id,
        'created_by' => $user->id,
        'meeting_date' => $calendarDate,
        'status' => 'Cancelled',
    ]);
    Meeting::factory()->create([
        'boardroom_id' => $boardroom->id,
        'created_by' => $user->id,
        'meeting_date' => now()->addDays(4)->format('Y-m-d'),
    ]);

    $this->actingAs($user)
        ->get(route('staff.dashboard', [
            'current_team' => $user->currentTeam->slug,
            'date' => $calendarDate,
        ]))
        ->assertOk()
        ->assertViewHas('calendarDate', $calendarDate)
        ->assertViewHas('dailyMeetings', function ($dailyMeetings) use ($meeting, $laterMeeting) {
            return $dailyMeetings->modelKeys() === [$meeting->id, $laterMeeting->id]
                && $dailyMeetings->every(fn ($dailyMeeting) => $dailyMeeting->relationLoaded('boardroom')
                    && $dailyMeeting->relationLoaded('creator'));
        })
        ->assertViewHas('dailyMeetings', fn ($dailyMeetings) => ! $dailyMeetings->contains('id', $cancelledMeeting->id));
});
