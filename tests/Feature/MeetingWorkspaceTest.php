<?php

use App\Enums\TeamRole;
use App\Models\Boardroom;
use App\Models\Meeting;
use App\Models\MeetingDocument;
use App\Models\MeetingRsvp;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function createMeetingWorkspace(User $creator): Meeting
{
    return Meeting::factory()->create([
        'boardroom_id' => Boardroom::factory(),
        'created_by' => $creator->id,
    ]);
}

test('meeting workspace renders the governance forms and recorded details', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $meeting = createMeetingWorkspace($user);

    $this->actingAs($user)
        ->get(route('meetings.show', [
            'current_team' => $user->currentTeam->slug,
            'meeting' => $meeting->id,
        ]))
        ->assertOk()
        ->assertViewIs('meetings.show')
        ->assertSee('Attendance & RSVP')
        ->assertSee('Upload Document')
        ->assertSee('Save Governance Records');
});

test('staff can access the booking creation page with available boardrooms', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $availableRoom = Boardroom::factory()->create(['name' => 'Available Room', 'status' => 'Available']);
    Boardroom::factory()->create(['name' => 'Maintenance Room', 'status' => 'Maintenance']);

    $this->actingAs($user)
        ->get(route('bookings.create', ['current_team' => $user->currentTeam->slug]))
        ->assertOk()
        ->assertViewIs('staff.bookings.create')
        ->assertSee('Create Boardroom Booking')
        ->assertSee('Available Room')
        ->assertDontSee('Maintenance Room')
        ->assertSee(route('meetings.store', ['current_team' => $user->currentTeam]), false);
});

test('daily availability links prefill the selected booking slot', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $boardroom = Boardroom::factory()->create(['status' => 'Available']);
    $date = now()->addDays(3)->format('Y-m-d');

    $this->actingAs($user)
        ->get(route('bookings.create', [
            'current_team' => $user->currentTeam->slug,
            'boardroom_id' => $boardroom->id,
            'meeting_date' => $date,
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]))
        ->assertOk()
        ->assertSee('value="'.$boardroom->id.'" selected', false)
        ->assertSee('value="'.$date.'"', false)
        ->assertSee('value="08:00"', false)
        ->assertSee('value="09:00"', false);
});

test('meeting creator can save governance records and their own RSVP', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $meeting = createMeetingWorkspace($user);
    $routeParameters = [
        'current_team' => $user->currentTeam->slug,
        'meeting' => $meeting->id,
    ];

    $this->actingAs($user)
        ->patch(route('meetings.minutes.update', $routeParameters), [
            'agenda' => 'Review the annual plan.',
            'minutes' => 'The plan was approved.',
            'action_items' => 'Finance team to share the budget.',
        ])
        ->assertRedirect();

    expect($meeting->fresh()->agenda)->toBe('Review the annual plan.')
        ->and($meeting->fresh()->minutes)->toBe('The plan was approved.')
        ->and($meeting->fresh()->action_items)->toBe('Finance team to share the budget.');

    $this->post(route('meetings.rsvp', $routeParameters), [
        'status' => 'Attending',
        'notes' => 'I will attend.',
    ])->assertRedirect();

    expect(MeetingRsvp::query()
        ->where('meeting_id', $meeting->id)
        ->where('user_id', $user->id)
        ->value('status'))->toBe('Attending');
});

test('meeting creator can edit a reservation when the new time is available', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $meeting = createMeetingWorkspace($user);
    $newBoardroom = Boardroom::factory()->create(['name' => 'Strategy Room']);
    $routeParameters = [
        'current_team' => $user->currentTeam->slug,
        'meeting' => $meeting->id,
    ];

    $this->actingAs($user)
        ->get(route('meetings.edit', $routeParameters))
        ->assertOk()
        ->assertViewIs('meetings.edit')
        ->assertSee('Edit / Reschedule Reservation')
        ->assertSee('Modify Booking')
        ->assertSee('value="PATCH"', false)
        ->assertSee('Strategy Room');

    $this->patch(route('meetings.update', $routeParameters), [
        'title' => 'Updated Planning Meeting',
        'boardroom_id' => $newBoardroom->id,
        'meeting_date' => now()->addDays(2)->format('Y-m-d'),
        'start_time' => '11:00',
        'end_time' => '12:30',
        'agenda' => 'Review the revised plan.',
    ])
        ->assertRedirect(route('meetings.show', $routeParameters))
        ->assertSessionHas('success');

    expect($meeting->fresh()->title)->toBe('Updated Planning Meeting')
        ->and($meeting->fresh()->boardroom_id)->toBe($newBoardroom->id)
        ->and($meeting->fresh()->agenda)->toBe('Review the revised plan.');
});

test('meeting edits are rejected when the new schedule overlaps another booking', function () {
    $user = User::factory()->create(['role' => 'staff']);
    $meeting = createMeetingWorkspace($user);
    $conflictingMeeting = Meeting::factory()->create([
        'boardroom_id' => $meeting->boardroom_id,
        'meeting_date' => now()->addDays(2)->format('Y-m-d'),
        'start_time' => '10:00',
        'end_time' => '11:00',
    ]);
    $routeParameters = [
        'current_team' => $user->currentTeam->slug,
        'meeting' => $meeting->id,
    ];

    $this->actingAs($user)
        ->patch(route('meetings.update', $routeParameters), [
            'title' => 'Conflicting Update',
            'boardroom_id' => $meeting->boardroom_id,
            'meeting_date' => $conflictingMeeting->meeting_date->format('Y-m-d'),
            'start_time' => '10:30',
            'end_time' => '11:30',
        ])
        ->assertSessionHasErrors('conflict');

    expect($meeting->fresh()->title)->not->toBe('Conflicting Update');
});

test('staff cannot edit another users reservation', function () {
    $creator = User::factory()->create(['role' => 'staff']);
    $otherUser = User::factory()->create(['role' => 'staff']);
    $meeting = createMeetingWorkspace($creator);
    $routeParameters = [
        'current_team' => $creator->currentTeam->slug,
        'meeting' => $meeting->id,
    ];

    $this->actingAs($otherUser)
        ->get(route('meetings.edit', $routeParameters))
        ->assertForbidden();

    $this->patch(route('meetings.update', $routeParameters), [
        'title' => 'Unauthorized Update',
        'boardroom_id' => $meeting->boardroom_id,
        'meeting_date' => now()->addDays(2)->format('Y-m-d'),
        'start_time' => '11:00',
        'end_time' => '12:00',
    ])->assertForbidden();

    expect($meeting->fresh()->title)->not->toBe('Unauthorized Update');
});

test('staff cannot change another team member RSVP', function () {
    $creator = User::factory()->create(['role' => 'staff']);
    $team = $creator->currentTeam;
    $attendee = User::factory()->create(['role' => 'staff']);
    $team->members()->attach($attendee, ['role' => TeamRole::Member->value]);
    $meeting = createMeetingWorkspace($creator);

    $this->actingAs($attendee)
        ->post(route('meetings.rsvp', [
            'current_team' => $team->slug,
            'meeting' => $meeting->id,
        ]), [
            'user_id' => $creator->id,
            'status' => 'Declined',
        ])
        ->assertForbidden();

    expect(MeetingRsvp::query()->where('meeting_id', $meeting->id)->exists())->toBeFalse();
});

test('meeting members can upload and download workspace documents', function () {
    Storage::fake('local');
    $user = User::factory()->create(['role' => 'staff']);
    $meeting = createMeetingWorkspace($user);
    $routeParameters = [
        'current_team' => $user->currentTeam->slug,
        'meeting' => $meeting->id,
    ];

    $this->actingAs($user)
        ->post(route('meetings.documents.store', $routeParameters), [
            'title' => 'Board Pack',
            'document' => UploadedFile::fake()->createWithContent('board-pack.pdf', '%PDF-1.4 test document'),
        ])
        ->assertRedirect();

    $document = MeetingDocument::query()->where('meeting_id', $meeting->id)->firstOrFail();
    Storage::disk('local')->assertExists($document->file_path);

    $this->get(route('meetings.documents.download', [
        ...$routeParameters,
        'document' => $document->id,
    ]))
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=board-pack.pdf');
});
