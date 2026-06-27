<?php
use App\Http\Controllers\Admin\AuthController;
use Illuminate\Support\Facades\Route;

// Admin auth
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout']);

// Admin protégé
Route::middleware('admin.auth')->prefix('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class)->except(['show']);
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class)->except(['show']);
    Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class)->except(['show']);
    Route::get('contacts', [\App\Http\Controllers\Admin\ContactController::class, 'index']);
    Route::get('contacts/{contact}/pdf', [\App\Http\Controllers\Admin\ContactController::class, 'pdf']);
    Route::delete('contacts/{contact}', [\App\Http\Controllers\Admin\ContactController::class, 'destroy']);
});

// Catch-all SPA React — doit être EN DERNIER
Route::get('/{any}', function () {
    $file = public_path('build/index.html');
    if (!file_exists($file)) {
        return response('Frontend not built. Run: cd frontend && npm run build', 503);
    }
    return response()->file($file);
})->where('any', '^(?!api|admin).*$');
