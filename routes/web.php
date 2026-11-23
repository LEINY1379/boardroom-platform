<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', WelcomeController::class)->name('home');

// Guest Login Routes (Connected to your login.blade.php form)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout Route
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Team-Scoped Authenticated Routes
Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {

        Route::get('meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
        Route::patch('meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
        Route::get('meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
        Route::post('meetings/{meeting}/rsvp', [MeetingController::class, 'updateRsvp'])->name('meetings.rsvp');
        Route::post('meetings/{meeting}/documents', [MeetingController::class, 'uploadDocument'])->name('meetings.documents.store');
        Route::get('meetings/{meeting}/documents/{document}/download', [MeetingController::class, 'downloadDocument'])->name('meetings.documents.download');
        Route::patch('meetings/{meeting}/minutes', [MeetingController::class, 'updateMinutes'])->name('meetings.minutes.update');

        // Normal Staff -> resources/views/staff/dashboard.blade.php
        Route::middleware('role:staff')->group(function () {
            Route::get('dashboard', [MeetingController::class, 'staffDashboard'])->name('staff.dashboard');
            Route::get('bookings/create', [MeetingController::class, 'create'])->name('bookings.create');
            Route::post('meetings', [MeetingController::class, 'store'])->name('meetings.store');
            Route::delete('meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');
        });

        // Admin -> resources/views/admin/dashboard.blade.php
        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
            // Overview Dashboard
            Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

            // Boardroom provisioning and controls
            Route::get('boardrooms/create', [AdminController::class, 'createBoardroom'])->name('boardrooms.create');
            Route::post('boardrooms', [AdminController::class, 'storeBoardroom'])->name('boardrooms.store');
            Route::patch('boardrooms/{boardroom}/toggle', [AdminController::class, 'toggleBoardroomStatus'])->name('boardrooms.toggle');
            Route::delete('boardrooms/{boardroom}', [AdminController::class, 'destroyBoardroom'])->name('boardrooms.destroy');

            // User provisioning and role assignment
            Route::get('users/create', [AdminController::class, 'createUser'])->name('users.create');
            Route::post('users', [AdminController::class, 'storeUser'])->name('users.store');
            Route::patch('users/{user}/role', [AdminController::class, 'toggleUserRole'])->name('users.role');
            Route::delete('users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

            // Admin booking and override actions
            Route::get('bookings/create', [AdminController::class, 'createBooking'])->name('bookings.create');
            Route::post('bookings', [MeetingController::class, 'store'])->name('bookings.store');
            Route::patch('meetings/{meeting}/override', [AdminController::class, 'overrideMeeting'])->name('meetings.override');
        });
    });

require __DIR__.'/settings.php';
