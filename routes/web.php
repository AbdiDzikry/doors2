<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Master\ExternalParticipantController;
use App\Http\Controllers\Master\PantryItemController;
use App\Http\Controllers\Master\RoomController;
use App\Http\Controllers\Master\PriorityGuestController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Meeting\RoomReservationController;
use App\Http\Controllers\Meeting\RecurringMeetingController;
use App\Http\Controllers\Meeting\MeetingListController;
use App\Http\Controllers\Meeting\AnalyticsController;
use App\Http\Controllers\Meeting\BookingController;
use App\Http\Controllers\Settings\ConfigurationController;
use App\Http\Controllers\Settings\RolePermissionController;
use App\Http\Controllers\Dashboard\ReceptionistDashboardController;
use App\Http\Controllers\Meeting\UserBookingController;
// use App\Http\Controllers\GuideController;
use App\Http\Controllers\SurveyController;


// General Affair Public Routes (No Auth)
Route::prefix('ga')->name('ga.')->group(function () {
    Route::get('/report-asset/{uuid}', [App\Http\Controllers\GeneralAffair\PublicReportController::class, 'show'])->name('report.show');
    Route::post('/report-asset/{uuid}', [App\Http\Controllers\GeneralAffair\PublicReportController::class, 'store'])->name('report.store');
});

Route::redirect('/', '/login');

// SSO Routes
// SSO Routes
Route::middleware('web')->group(function () {
    Route::get('/sso', [App\Http\Controllers\Auth\SSOController::class, 'login'])->name('sso.login');
    // Deprecated routes kept commented for reference if needed
    // Route::get('/login/sso', [App\Http\Controllers\Auth\SSOController::class, 'login'])->name('sso.login.legacy');
});

// User Tour Routes
Route::post('/tour/mark-as-seen', [App\Http\Controllers\TourController::class, 'markAsSeen'])
    ->name('tour.mark-seen')
    ->middleware('auth');

// Public booking page for users (no auth required)
Route::prefix('user-booking')->name('user-booking.')->group(function () {
    Route::get('/', [UserBookingController::class, 'index'])->name('index');
    // User Guide
    // Route::get('/guide', [GuideController::class, 'index'])->name('guide.index');

    Route::get('/search', [UserBookingController::class, 'search'])->name('search');
    Route::post('/select', [UserBookingController::class, 'select'])->name('select');
});



Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('master/rooms/images/{filename}', [RoomController::class, 'getImage'])->name('master.rooms.image');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::view('/guide', 'guide.index')->name('guide.index');
    Route::get('/survey/give', [SurveyController::class, 'create'])->name('survey.create');
    Route::post('/survey', [SurveyController::class, 'store'])->name('survey.store');
    Route::get('/survey/results', [SurveyController::class, 'index'])->name('survey.index');
});

Route::middleware(['auth', 'can:manage master data'])->name('master.')->prefix('master')->group(function () {
    // ... existing routes
    Route::get('external-participants/template', [ExternalParticipantController::class, 'downloadTemplate'])->name('external-participants.template');
    Route::post('external-participants/import', [ExternalParticipantController::class, 'import'])->name('external-participants.import');
    Route::get('external-participants/export', [ExternalParticipantController::class, 'export'])->name('external-participants.export');
    Route::resource('external-participants', ExternalParticipantController::class);
    Route::resource('rooms', RoomController::class);
    Route::resource('priority-guests', PriorityGuestController::class);
    Route::get('users/contact-template', [UserController::class, 'downloadContactTemplate'])->name('users.contact-template');
    Route::post('users/import-contacts', [UserController::class, 'importContacts'])->name('users.import-contacts');
    Route::get('users/template', [UserController::class, 'downloadTemplate'])->name('users.template');
    Route::post('users/import', [UserController::class, 'import'])->name('users.import');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export');
    Route::post('users/sync', [UserController::class, 'syncFromApi'])->name('users.sync');
    Route::resource('users', UserController::class);
    Route::resource('recurring-meetings', RecurringMeetingController::class);
});

Route::middleware(['auth', 'can:manage pantry'])->name('master.')->prefix('master')->group(function () {
    Route::resource('pantry-items', PantryItemController::class);
});

// General Affair Admin Routes (Protected)
Route::middleware(['auth', 'can:manage assets'])->prefix('ga')->name('ga.')->group(function () {
    Route::get('/assets', [App\Http\Controllers\GeneralAffair\AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/{uuid}/qr', [App\Http\Controllers\GeneralAffair\AssetController::class, 'printQr'])->name('assets.qr');
});

Route::middleware(['auth', 'verified'])->prefix('meeting')->name('meeting.')->group(function () {

    Route::resource('room-reservations', RoomReservationController::class);
    Route::resource('bookings', BookingController::class);
    Route::resource('meeting-lists', MeetingListController::class)->parameters([
        'meeting-lists' => 'meeting'
    ]);
    Route::post('/meetings/{meeting}/attendance', [App\Http\Controllers\Meeting\MeetingAttendanceController::class, 'store'])->name('meetings.attendance.store');
    Route::get('/meetings/{meeting}/attendance/export', [MeetingListController::class, 'exportAttendance'])->name('meetings.attendance.export');
    Route::get('/meetings/{meeting}/attendance/export-pdf', [MeetingListController::class, 'exportAttendancePdf'])->name('meetings.attendance.export-pdf');
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
});

// Settings routes requiring specific permissions
Route::name('settings.')->prefix('settings')->group(function () {
    Route::middleware(['auth', 'can:manage configurations'])->group(function () {
        Route::put('configurations/bulk', [ConfigurationController::class, 'updateBulk'])->name('configurations.update-bulk');
        Route::resource('configurations', ConfigurationController::class);
    });
    Route::resource('role-permissions', RolePermissionController::class)->parameters(['role-permissions' => 'role'])->middleware(['auth', 'can:manage roles and permissions']);
    Route::middleware(['auth', 'can:manage settings'])->group(function () {
        Route::get('input-code', [App\Http\Controllers\Settings\InputCodeController::class, 'index'])->name('input-code.index');
        Route::post('input-code/unlock', [App\Http\Controllers\Settings\InputCodeController::class, 'unlock'])->name('input-code.unlock');
        Route::post('input-code', [App\Http\Controllers\Settings\InputCodeController::class, 'execute'])->name('input-code.execute');
    });
});

Route::middleware(['auth', 'can:access pantry dashboard'])->name('dashboard.')->prefix('dashboard')->group(function () {
    Route::get('/receptionist', [ReceptionistDashboardController::class, 'index'])->name('receptionist');
    Route::put('/receptionist/pantry-orders/{pantry_order}/update', [ReceptionistDashboardController::class, 'update'])->name('receptionist.pantry-orders.update');
    Route::put('/receptionist/meetings/{meeting}/pantry-status', [ReceptionistDashboardController::class, 'updatePantryForMeeting'])->name('receptionist.meetings.pantry-status');
    Route::get('/receptionist/pantry-orders-partial', [ReceptionistDashboardController::class, 'getPantryOrdersPartial'])->name('receptionist.pantry-orders-partial');
});

// Tablet Kiosk Mode (Protected by Role/Permission)
Route::middleware(['auth', 'can:access tablet mode'])->prefix('tablet')->name('tablet.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\TabletController::class, 'index'])->name('index');
    Route::get('/room/{id}', [App\Http\Controllers\TabletController::class, 'show'])->name('show');
    Route::post('/room/{id}/book', [App\Http\Controllers\TabletController::class, 'store'])->name('book');
    Route::post('/room/meeting/{id}/check-in', [App\Http\Controllers\TabletController::class, 'checkIn'])->name('check-in');
    Route::post('/room/meeting/{id}/cancel', [App\Http\Controllers\TabletController::class, 'cancel'])->name('cancel');
    Route::post('/battery-alert', [App\Http\Controllers\TabletController::class, 'batteryAlert'])->name('battery-alert');
    Route::get('/check-updates/{id}', [App\Http\Controllers\TabletController::class, 'checkUpdates'])->name('check-updates');
});

// Temporary Debug Route
Route::get('/debug-perms', function () {
    $user = auth()->user();
    return [
        'name' => $user->name,
        'roles' => $user->getRoleNames(),
        'permissions' => $user->getAllPermissions()->pluck('name'),
        'can_access_tablet' => $user->can('access tablet mode')
    ];
})->middleware('auth');


// General Affair Routes (Assets & Tickets)
Route::middleware(['auth', 'can:manage assets'])->prefix('ga')->name('ga.')->group(function () {
    Route::get('/assets', [App\Http\Controllers\GeneralAffair\AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/{uuid}/qr', [App\Http\Controllers\GeneralAffair\AssetController::class, 'printQr'])->name('assets.qr');

    Route::get('/tickets', [App\Http\Controllers\GeneralAffair\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{uuid}', [App\Http\Controllers\GeneralAffair\TicketController::class, 'show'])->name('tickets.show');

    // Ticket Actions
    Route::post('/tickets/{uuid}/validate', [App\Http\Controllers\GeneralAffair\TicketController::class, 'validateTicket'])->name('tickets.validate');
    Route::post('/tickets/{uuid}/reject', [App\Http\Controllers\GeneralAffair\TicketController::class, 'rejectTicket'])->name('tickets.reject');
    Route::post('/tickets/{uuid}/assign', [App\Http\Controllers\GeneralAffair\TicketController::class, 'assignTicket'])->name('tickets.assign');
    Route::post('/tickets/{uuid}/resolve', [App\Http\Controllers\GeneralAffair\TicketController::class, 'resolveTicket'])->name('tickets.resolve');
});

require __DIR__ . '/auth.php';
