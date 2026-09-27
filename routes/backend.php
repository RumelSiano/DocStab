<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentTransitionController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->name('api.')->group(function (): void {
    Route::get('csrf', fn () => response()->json(['token' => csrf_token()]))->name('csrf');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login');
    Route::post('forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:3,1')->name('password.forgot');
    Route::post('reset-password', [AuthController::class, 'reset'])->middleware('throttle:6,1')->name('password.reset');
    Route::middleware(EnsureActiveUser::class)->group(function (): void {
        Route::get('me', fn (Request $request) => response()->json((new UserResource($request->user()))->resolve($request)))->name('me');
        Route::patch('me', [AuthController::class, 'profile'])->name('profile.update');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('catalogs', [CatalogController::class, 'index'])->name('catalogs.index');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('requests', [RequestController::class, 'index'])->name('requests.index');
        Route::post('requests', [RequestController::class, 'store'])->name('requests.store');
        Route::get('requests/{documentRequest}', [RequestController::class, 'show'])->whereNumber('documentRequest')->name('requests.show');
        Route::post('requests/{documentRequest}/documents/{document}/transition', [DocumentTransitionController::class, 'store'])->scopeBindings()->whereNumber(['documentRequest', 'document'])->name('documents.transition');
        Route::post('requests/{documentRequest}/releases', [ReleaseController::class, 'store'])->whereNumber('documentRequest')->name('releases.store');
        Route::post('requests/{documentRequest}/feedback', [FeedbackController::class, 'store'])->whereNumber('documentRequest')->name('feedback.store');
        Route::post('requests/{documentRequest}/attachments', [AttachmentController::class, 'store'])->whereNumber('documentRequest')->middleware('throttle:30,1')->name('attachments.store');
        Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->whereNumber('attachment')->name('attachments.download');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('admin/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('admin/users/{user}', [UserController::class, 'update'])->whereNumber('user')->name('users.update');
        Route::post('admin/catalogs/{resource}', [CatalogController::class, 'store'])->name('catalogs.store');
        Route::patch('admin/catalogs/{resource}/{id}', [CatalogController::class, 'update'])->whereNumber('id')->name('catalogs.update');
        Route::get('history', [HistoryController::class, 'index'])->name('history');
        Route::get('admin/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    });
});
