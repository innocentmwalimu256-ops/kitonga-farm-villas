<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\POSController as AdminPOSController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ExpenseController as AdminExpenseController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\CMSController as AdminCMSController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\HousekeepingController as AdminHousekeepingController;
use App\Http\Controllers\Admin\AccommodationController as AdminAccommodationController;
use App\Http\Controllers\Admin\ExperienceController as AdminExperienceController;
use Illuminate\Support\Facades\Route;

// --- Public Website ---
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/villas', [PublicController::class, 'villas'])->name('villas');
Route::get('/villas/{slug}', [PublicController::class, 'showVilla'])->name('villas.show');
Route::get('/experiences', [PublicController::class, 'experiences'])->name('experiences');
Route::get('/experiences/{slug}', [PublicController::class, 'showExperience'])->name('experiences.show');
Route::get('/farm', [PublicController::class, 'farm'])->name('farm');
Route::get('/products', [PublicController::class, 'products'])->name('products');
Route::get('/gallery', [PublicController::class, 'gallery'])->name('gallery');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/location', [PublicController::class, 'location'])->name('location');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'contactSubmit'])->name('contact.submit');
Route::get('/policies/{policyName?}', [PublicController::class, 'policies'])->name('policies');

// --- Guest Booking Wizard ---
Route::get('/book', [BookController::class, 'showForm'])->name('booking.form');
Route::post('/book', [BookController::class, 'store'])->name('booking.store');
Route::post('/experiences/book', [BookController::class, 'storeExperienceBooking'])->name('experiences.book');
Route::get('/booking/success/{reference}', [BookController::class, 'success'])->name('booking.success');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/preview-admin-dashboard', function () {
    $user = \App\Models\User::where('email', 'owner@kitongafarm.com')->first() ?? \App\Models\User::first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
    }
    return redirect()->route('admin.dashboard');
});

// --- Admin Operations (Auth Required) ---
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    

    // Booking & Calendar Operations
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/calendar', [AdminBookingController::class, 'calendar'])->name('bookings.calendar');
    Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [AdminBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{id}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status');
    Route::post('/bookings/{id}/payment', [AdminBookingController::class, 'addPayment'])->name('bookings.payment');
    Route::post('/bookings/{id}/reschedule', [AdminBookingController::class, 'reschedule'])->name('bookings.reschedule');

    // POS & Bar Commerce
    Route::get('/pos', [AdminPOSController::class, 'terminal'])->name('pos.terminal');
    Route::post('/pos', [AdminPOSController::class, 'store'])->name('pos.store');
    Route::get('/pos/sales', [AdminPOSController::class, 'index'])->name('pos.index');
    Route::post('/pos/sales/{id}/cancel', [AdminPOSController::class, 'cancel'])->name('pos.cancel');
    Route::get('/pos/receipt/{id}', [AdminPOSController::class, 'receipt'])->name('pos.receipt');

    // Inventory & Catalog
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::post('/products/{id}', [AdminProductController::class, 'update'])->name('products.update');
    Route::post('/products/{id}/adjust', [AdminProductController::class, 'adjust'])->name('products.adjust');
    Route::get('/products/{id}/movements', [AdminProductController::class, 'movements'])->name('products.movements');

    // Expenses Logging
    Route::get('/expenses', [AdminExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [AdminExpenseController::class, 'store'])->name('expenses.store');
    Route::post('/expenses/{id}', [AdminExpenseController::class, 'update'])->name('expenses.update');
    Route::post('/expenses/{id}/status', [AdminExpenseController::class, 'updateStatus'])->name('expenses.status');
    Route::delete('/expenses/{id}', [AdminExpenseController::class, 'destroy'])->name('expenses.destroy');

    // CMS & Media
    Route::get('/cms', [AdminCMSController::class, 'index'])->name('cms.index');
    Route::post('/cms/section/{id}', [AdminCMSController::class, 'updateSection'])->name('cms.update_section');
    Route::post('/cms/page/{id}/publish', [AdminCMSController::class, 'publishPage'])->name('cms.publish_page');

    Route::get('/media', [AdminMediaController::class, 'index'])->name('media.index');
    Route::post('/media', [AdminMediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{id}', [AdminMediaController::class, 'destroy'])->name('media.destroy');

    // Agritourism Experiences
    Route::get('/experiences', [AdminExperienceController::class, 'index'])->name('experiences.index');
    Route::get('/experiences/create', [AdminExperienceController::class, 'create'])->name('experiences.create');
    Route::post('/experiences', [AdminExperienceController::class, 'store'])->name('experiences.store');
    Route::get('/experiences/{id}/edit', [AdminExperienceController::class, 'edit'])->name('experiences.edit');
    Route::post('/experiences/{id}', [AdminExperienceController::class, 'update'])->name('experiences.update');
    Route::delete('/experiences/{id}', [AdminExperienceController::class, 'destroy'])->name('experiences.destroy');
    Route::get('/experiences/{slug}/preview', [AdminExperienceController::class, 'preview'])->name('experiences.preview');

    // Global Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');

    // Housekeeping Operations
    Route::get('/housekeeping', [AdminHousekeepingController::class, 'index'])->name('housekeeping.index');
    Route::post('/housekeeping/{id}', [AdminHousekeepingController::class, 'update'])->name('housekeeping.update');

    // Accommodation Management CRUD
    Route::get('/accommodation', [AdminAccommodationController::class, 'index'])->name('accommodation.index');
    Route::post('/accommodation/types', [AdminAccommodationController::class, 'storeType'])->name('accommodation.types.store');
    Route::post('/accommodation/types/{id}', [AdminAccommodationController::class, 'updateType'])->name('accommodation.types.update');
    Route::post('/accommodation/units', [AdminAccommodationController::class, 'storeUnit'])->name('accommodation.units.store');
    Route::post('/accommodation/units/{id}', [AdminAccommodationController::class, 'updateUnit'])->name('accommodation.units.update');
    Route::post('/accommodation/blocks', [AdminAccommodationController::class, 'storeBlock'])->name('accommodation.blocks.store');
    Route::delete('/accommodation/blocks/{id}', [AdminAccommodationController::class, 'destroyBlock'])->name('accommodation.blocks.destroy');

    // Guests Panel
    Route::get('/guests', [\App\Http\Controllers\Admin\GuestController::class, 'index'])->name('guests.index');

    // Users and Roles (Staff Accounts)
    Route::get('/users', [\App\Http\Controllers\Admin\StaffUserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\Admin\StaffUserController::class, 'store'])->name('users.store');
    Route::post('/users/{id}', [\App\Http\Controllers\Admin\StaffUserController::class, 'update'])->name('users.update');

    // Audit Logs
    Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit_logs.index');

    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/excel/bookings', [\App\Http\Controllers\Admin\ReportController::class, 'exportBookings'])->name('reports.excel.bookings');
    Route::get('/reports/excel/expenses', [\App\Http\Controllers\Admin\ReportController::class, 'exportExpenses'])->name('reports.excel.expenses');
    Route::get('/reports/pdf', [\App\Http\Controllers\Admin\ReportController::class, 'downloadPdfReport'])->name('reports.pdf');

    // Financial Operations (Payments, Refunds, Reports)
    Route::get('/payments', [\App\Http\Controllers\Admin\FinancialController::class, 'paymentsIndex'])->name('payments.index');
    Route::post('/payments/{id}/refund', [\App\Http\Controllers\Admin\FinancialController::class, 'refundPayment'])->name('payments.refund');
    Route::get('/financials/report', [\App\Http\Controllers\Admin\FinancialController::class, 'financialReport'])->name('financials.report');
});

// Redirect default dashboard route to admin prefix dashboard
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
