<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Customer\CustomerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Landing page untuk guest; jika user sudah login akan otomatis diarahkan
| ke dashboard sesuai role. Auth scaffolding dari laravel/ui juga dipakai.
|
*/

// Landing page: tampilkan landing untuk guest, redirect kalau sudah login
Route::get('/', function () {
    if (auth()->check()) {
        // redirect sesuai role jika sudah login
        $role = auth()->user()->role ?? null;
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($role === 'customer') {
            return redirect()->route('customer.dashboard');
        }

        // fallback bila role tidak ter-set
        return redirect()->route('home');
    }

    return view('home'); // resources/views/landing.blade.php
})->name('landing');

// Auth routes (dihasilkan oleh laravel/ui)
// Auth::routes();

// Optional: route legacy /home
Route::get('/home', [HomeController::class, 'index'])
    ->name('home')
    ->middleware(['auth', 'role:admin,customer']);

// Group route khusus ADMIN
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    // tambah route admin lain di sini...
});

// Group route khusus CUSTOMER
Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    // tambah route customer lain di sini...
});

// Route yang bisa diakses kedua role: redirect ke masing-masing dashboard
Route::get('/dashboard', function () {
    $role = auth()->user()->role ?? null;
    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($role === 'customer') {
        return redirect()->route('customer.dashboard');
    }

    // fallback
    return redirect()->route('home');
})->name('dashboard')->middleware(['auth', 'role:admin,customer']);

// Halaman lain untuk semua (guest & auth)
Route::view('/about', 'about')->name('about');

use App\Http\Controllers\Auth\LogoutController;

// Route::post('/logout', LogoutController::class)->name('logout');
