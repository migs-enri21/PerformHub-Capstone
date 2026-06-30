<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\OnboardingController;
use App\Http\Controllers\TalentProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Organizer\BookingController as OrganizerBookingController;
use App\Http\Controllers\Organizer\DashboardController as OrganizerDashboardController;
use App\Http\Controllers\Organizer\InterviewController as OrganizerInterviewController;
use App\Http\Controllers\Organizer\PerformerSearchController;
use App\Http\Controllers\Organizer\ProfileController as OrganizerProfileController;
use App\Http\Controllers\Performer\AvailabilityController;
use App\Http\Controllers\Performer\BookingController as PerformerBookingController;
use App\Http\Controllers\Performer\DashboardController as PerformerDashboardController;
use App\Http\Controllers\Performer\PortfolioController;
use App\Http\Controllers\Performer\ProfileController as PerformerProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::get('/onboarding/role', [OnboardingController::class, 'showRole'])->name('onboarding.role');
    Route::post('/onboarding/role', [OnboardingController::class, 'storeRole'])->name('onboarding.role.store');
    Route::get('/onboarding/profile', [OnboardingController::class, 'showProfile'])->name('onboarding.profile');
    Route::post('/onboarding/profile', [OnboardingController::class, 'storeProfile'])->name('onboarding.profile.store');
    Route::get('/onboarding/verification', [OnboardingController::class, 'showVerification'])->name('onboarding.verification');
    Route::post('/onboarding/verification', [OnboardingController::class, 'storeVerification'])->name('onboarding.verification.store');
    Route::get('/onboarding/complete', [OnboardingController::class, 'showComplete'])->name('onboarding.complete');
    Route::post('/onboarding/dismiss-banner', [OnboardingController::class, 'dismissBanner'])->name('onboarding.dismiss-banner');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/interviews/{interview}/join', [InterviewController::class, 'join'])->name('interviews.join');
    Route::get('/talent/{performer}', [TalentProfileController::class, 'show'])->name('talent.show');
});

Route::middleware(['auth', 'role:performer'])->prefix('performer')->name('performer.')->group(function () {
    Route::get('/dashboard', [PerformerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [PerformerProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [PerformerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [PerformerProfileController::class, 'update'])->name('profile.update');
    Route::middleware('full.access')->group(function () {
        Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
        Route::post('/portfolio', [PortfolioController::class, 'store'])->name('portfolio.store');
        Route::delete('/portfolio/{portfolio}', [PortfolioController::class, 'destroy'])->name('portfolio.destroy');
        Route::get('/availability', [AvailabilityController::class, 'index'])->name('availability.index');
        Route::post('/availability', [AvailabilityController::class, 'store'])->name('availability.store');
        Route::delete('/availability/{schedule}', [AvailabilityController::class, 'destroy'])->name('availability.destroy');
        Route::post('/bookings/{booking}/accept', [PerformerBookingController::class, 'accept'])->name('bookings.accept');
        Route::post('/bookings/{booking}/reject', [PerformerBookingController::class, 'reject'])->name('bookings.reject');
        Route::post('/bookings/{booking}/confirm-contract', [PerformerBookingController::class, 'confirmContract'])->name('bookings.confirm-contract');
    });
    Route::get('/bookings', [PerformerBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [PerformerBookingController::class, 'show'])->name('bookings.show');
});

Route::middleware(['auth', 'role:organizer'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/dashboard', [OrganizerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [OrganizerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [OrganizerProfileController::class, 'update'])->name('profile.update');
    Route::get('/performers', [PerformerSearchController::class, 'index'])->name('performers.index');
    Route::get('/performers/{performer}', [PerformerSearchController::class, 'show'])->name('performers.show');
    Route::get('/bookings', [OrganizerBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [OrganizerBookingController::class, 'show'])->name('bookings.show');
    Route::middleware('full.access')->group(function () {
        Route::get('/bookings/create/{performer}', [OrganizerBookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings/{performer}', [OrganizerBookingController::class, 'store'])->name('bookings.store');
        Route::post('/bookings/{booking}/contract', [OrganizerBookingController::class, 'uploadContract'])->name('bookings.contract');
        Route::post('/bookings/{booking}/complete', [OrganizerBookingController::class, 'complete'])->name('bookings.complete');
        Route::get('/bookings/{booking}/interview', [OrganizerInterviewController::class, 'create'])->name('interviews.create');
        Route::post('/bookings/{booking}/interview', [OrganizerInterviewController::class, 'store'])->name('interviews.store');
    });
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/verify', [AdminUserController::class, 'verify'])->name('users.verify');
    Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggleActive'])->name('users.toggle');
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/monitoring/bookings', [MonitoringController::class, 'bookings'])->name('monitoring.bookings');
    Route::get('/monitoring/interviews', [MonitoringController::class, 'interviews'])->name('monitoring.interviews');
});
