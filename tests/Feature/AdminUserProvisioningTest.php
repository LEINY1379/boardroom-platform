<?php

use App\Enums\TeamRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin can create an account for a team member', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $team = $admin->currentTeam;

    $response = $this
        ->actingAs($admin)
        ->withHeader('referer', route('admin.dashboard', ['current_team' => $team->slug]))
        ->post(route('admin.users.store', ['current_team' => $team->slug]), [
            'name' => 'New Staff Member',
            'email' => 'new.staff@example.com',
            'password' => 'secure-password',
            'role' => 'staff',
        ]);

    $response
        ->assertRedirect(route('admin.dashboard', ['current_team' => $team->slug]))
        ->assertSessionHas('success', 'Account created for New Staff Member (staff).');

    $newUser = User::where('email', 'new.staff@example.com')->firstOrFail();

    expect($newUser->name)->toBe('New Staff Member')
        ->and($newUser->role)->toBe('staff')
        ->and(Hash::check('secure-password', $newUser->password))->toBeTrue()
        ->and($newUser->current_team_id)->toBe($team->id)
        ->and($newUser->teamRole($team))->toBe(TeamRole::Member);

    $this->post(route('logout'));

    $this->post(route('login.post'), [
        'email' => 'new.staff@example.com',
        'password' => 'secure-password',
    ])->assertRedirect(route('staff.dashboard', ['current_team' => $team->slug]));

    $this->get(route('staff.dashboard', ['current_team' => $team->slug]))
        ->assertOk()
        ->assertSee('Welcome back, New Staff Member');
});

test('admin user creation rejects duplicate emails and invalid roles', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $team = $admin->currentTeam;
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.dashboard', ['current_team' => $team->slug]))
        ->post(route('admin.users.store', ['current_team' => $team->slug]), [
            'name' => 'Duplicate User',
            'email' => $existingUser->email,
            'password' => 'secure-password',
            'role' => 'owner',
        ]);

    $response
        ->assertRedirect(route('admin.dashboard', ['current_team' => $team->slug]))
        ->assertSessionHasErrors(['email', 'role']);

    expect(User::where('email', $existingUser->email)->count())->toBe(1);
});
