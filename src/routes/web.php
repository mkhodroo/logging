<?php

use BehinLogging\Controllers\LoggingController;
use Illuminate\Support\Facades\Route;

Route::get('user-action-logs', [LoggingController::class, 'index'])
    ->middleware(['web', 'auth', 'access:گزارش لاگ کاربران'])
    ->name('user-action-logs.index');

Route::get('user-action-logs/{id}', [LoggingController::class, 'show'])
    ->whereNumber('id')
    ->middleware(['web', 'auth', 'access:گزارش لاگ کاربران'])
    ->name('user-action-logs.show');
