<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TelemetryController;
use App\Http\Controllers\UserController;

// ==========================================
// RUTE AUTENTIKASI (LOGIN, REGISTER, LUPA PASSWORD)
// ==========================================

// Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Register
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Forgot Password
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

// Reset Password
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Logout
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');


// ==========================================
// RUTE DASHBOARD MONITORING (PROTEKSI LOGIN)
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::get('/', [TelemetryController::class, 'index'])->name('home');
    Route::get('/dashboard', [TelemetryController::class, 'index'])->name('dashboard');

    // Manajemen Pengguna & Role (Hanya Admin)
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.updateRole');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});


// ==========================================
// RUTE API DATA SENSOR UNTUK ESP32 & REALTIME POLLING
// (Bebas CSRF & Tidak Memerlukan Sesi Login untuk ESP32)
// ==========================================

// 1. ESP32 mengirim data sensor ke endpoint ini (POST JSON/Form atau GET)
Route::match(['get', 'post'], '/api/data-sensor', [TelemetryController::class, 'record'])->name('api.sensor.record');
Route::match(['get', 'post'], '/api/sensor', [TelemetryController::class, 'record']);
Route::match(['get', 'post'], '/api/telemetry', [TelemetryController::class, 'record']);
Route::match(['get', 'post'], '/api/telemetry/record', [TelemetryController::class, 'record']);

// 2. Dashboard mengambil data sensor terbaru dari database
Route::get('/api/data-sensor/latest', [TelemetryController::class, 'latest'])->name('api.sensor.latest');
Route::get('/api/telemetry/latest', [TelemetryController::class, 'latest']);

// 3. Dashboard mengambil riwayat data sensor untuk tabel & grafik
Route::get('/api/data-sensor/history', [TelemetryController::class, 'history'])->name('api.sensor.history');
Route::get('/api/telemetry/history', [TelemetryController::class, 'history']);

// 4. Mengosongkan seluruh isi data sensor (Reset Database)
Route::match(['get', 'post'], '/api/data-sensor/clear', [TelemetryController::class, 'clear'])->name('api.sensor.clear');
Route::match(['get', 'post'], '/api/telemetry/clear', [TelemetryController::class, 'clear']);
