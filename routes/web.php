<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;

// Landing
Route::get('/', fn() => view('landing'))->name('landing');

// Auth test page (remove this after fixing)
Route::get('/test-auth', function() {
    $results = [
        'db_connected' => false,
        'users_found' => 0,
        'users' => [],
        'test_user' => null,
        'password_works' => false,
        'all_passwords_work' => false
    ];
    
    try {
        // Test database
        DB::connection()->getPdo();
        $results['db_connected'] = true;
        
        // Get users
        $users = DB::table('users')
            ->whereIn('email', ['owner@FreshTrack.ph', 'manager@FreshTrack.ph', 'cashier@FreshTrack.ph'])
            ->get();
        
        $results['users_found'] = $users->count();
        
        // Test each password
        foreach ($users as $user) {
            $works = Hash::check('password', $user->password);
            $results['users'][] = [
                'email' => $user->email,
                'role' => $user->role,
                'hash' => substr($user->password, 0, 40),
                'works' => $works
            ];
        }
        
        // Test owner account specifically
        $testUser = \App\Models\User::where('email', 'owner@FreshTrack.ph')->first();
        if ($testUser) {
            $results['test_user'] = [
                'email' => $testUser->email,
                'name' => $testUser->name,
                'role' => $testUser->role
            ];
            $results['password_works'] = Hash::check('password', $testUser->password);
        }
        
        // Check if all passwords work
        $results['all_passwords_work'] = count(array_filter($results['users'], fn($u) => $u['works'])) === count($results['users']);
        
    } catch (\Exception $e) {
        $results['error'] = $e->getMessage();
    }
    
    return view('test-auth', ['results' => $results]);
});

// Auth routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {
    
    // Owner only routes
    Route::middleware(['role:owner'])->group(function () {
        Route::get('/dashboard', fn() => view('pages.dashboard'))->name('dashboard');
        Route::get('/users', fn() => view('pages.users'))->name('users');
        Route::get('/decision-support', fn() => view('pages.decision-support'))->name('decision-support');
    });
    
    // Owner + Manager routes
    Route::middleware(['role:owner,manager'])->group(function () {
        Route::get('/inventory', fn() => view('pages.inventory'))->name('inventory');
        Route::get('/sales', fn() => view('pages.sales'))->name('sales');
        Route::get('/forecast', fn() => view('pages.forecast'))->name('forecast');
        Route::get('/spoilage', fn() => view('pages.spoilage'))->name('spoilage');
        Route::get('/analytics', fn() => view('pages.analytics'))->name('analytics');
        Route::get('/reports', fn() => view('pages.reports'))->name('reports');
    });
    
    // All authenticated users (Owner + Manager + Cashier)
    Route::get('/pos', fn() => view('pages.pos'))->name('pos');
    Route::get('/notifications', fn() => view('pages.notifications'))->name('notifications');
    Route::get('/settings', fn() => view('pages.settings'))->name('settings');
});

// Products API Routes (AJAX endpoints for Products module)
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::middleware(['role:owner,manager'])->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('api.products.store');
        Route::get('/products/{id}', [ProductController::class, 'show'])->name('api.products.show');
        Route::put('/products/{id}', [ProductController::class, 'update'])->name('api.products.update');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('api.products.destroy');
        Route::get('/products/categories/list', [ProductController::class, 'categories'])->name('api.products.categories');
    });
});
