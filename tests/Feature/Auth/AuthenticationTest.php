<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response
        ->assertOk()
        ->assertSee(route('login.post'), false)
        ->assertSee('id="password"', false)
        ->assertSee('type="password"', false)
        ->assertSee('id="toggle-password-visibility"', false)
        ->assertSee('aria-pressed="false"', false)
        ->assertSee('Show');
});

test('welcome page sends guests to the sign in screen', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee(route('login'), false)
        ->assertSee('Sign In to Portal');
});

test('authenticated home page restores a team when no current team is set', function () {
    $user = User::factory()->create();
    $team = $user->personalTeam();
    $user->forceFill(['current_team_id' => null])->save();
    $user = $user->fresh();

    $response = $this->actingAs($user)->get(route('home'));

    $response
        ->assertOk()
        ->assertSee(route('staff.dashboard', ['current_team' => $team->slug]), false)
        ->assertSee('data-test="logout-button"', false);

    expect($user->fresh()->current_team_id)->toBe($team->id);
});

test('authenticated home page creates a workspace when the user has no team memberships', function () {
    $user = User::factory()->create();
    $user->teams()->detach();
    $user->forceFill(['current_team_id' => null])->save();

    $response = $this->actingAs($user->fresh())->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Go to Your Dashboard')
        ->assertDontSee('Workspace setup required');

    expect($user->fresh()->currentTeam)->not->toBeNull();
});

test('custom login redirects to the users personal team when no current team is set', function () {
    $user = User::factory()->create();
    $team = $user->personalTeam();
    $user->forceFill(['current_team_id' => null])->save();

    $response = $this->withSession(['url.intended' => route('home')])->post(route('login.post'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertRedirect(route('staff.dashboard', ['current_team' => $team->slug]))
        ->assertSessionHasNoErrors();

    $this->get(route('staff.dashboard', ['current_team' => $team->slug]))
        ->assertOk()
        ->assertSee('Welcome back, '.$user->name);

    expect($user->fresh()->current_team_id)->toBe($team->id);
});

test('admin login redirects to the admin dashboard', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $team = $user->currentTeam;

    $response = $this->withSession(['url.intended' => route('home')])->post(route('login.post'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('admin.dashboard', ['current_team' => $team->slug]));

    $this->get(route('admin.dashboard', ['current_team' => $team->slug]))
        ->assertOk()
        ->assertViewIs('admin.dashboard')
        ->assertSee('Facility & Governance Oversight');
});

test('admins who visit the staff dashboard are redirected to the admin dashboard', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $team = $user->currentTeam;

    $response = $this->actingAs($user)->get(route('staff.dashboard', [
        'current_team' => $team->slug,
    ]));

    $response->assertRedirect(route('admin.dashboard', [
        'current_team' => $team->slug,
    ]));
});

test('custom login creates a workspace when the user has no team memberships', function () {
    $user = User::factory()->create();
    $user->teams()->detach();
    $user->forceFill(['current_team_id' => null])->save();

    $response = $this->post(route('login.post'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('staff.dashboard', [
        'current_team' => $user->fresh()->currentTeam->slug,
    ]));
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Sign In to Portal');
});
