<?php

use Illuminate\Support\Facades\Route;

// Landing
Route::get('/', fn() => view('landing'))->name('landing');

// Auth
Route::get('/login', fn() => view('auth.login'))->name('login');

// Main pages (protected by nothing — UI only prototype)
Route::get('/dashboard',        fn() => view('pages.dashboard'))->name('dashboard');
Route::get('/pos',              fn() => view('pages.pos'))->name('pos');
Route::get('/sales',            fn() => view('pages.sales'))->name('sales');
Route::get('/inventory',        fn() => view('pages.inventory'))->name('inventory');
Route::get('/forecast',         fn() => view('pages.forecast'))->name('forecast');
Route::get('/spoilage',         fn() => view('pages.spoilage'))->name('spoilage');
Route::get('/reports',          fn() => view('pages.reports'))->name('reports');
Route::get('/analytics',        fn() => view('pages.analytics'))->name('analytics');
Route::get('/users',            fn() => view('pages.users'))->name('users');
Route::get('/settings',         fn() => view('pages.settings'))->name('settings');
Route::get('/notifications',    fn() => view('pages.notifications'))->name('notifications');
