<?php

use App\Http\Controllers\AliasController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MailboxController;
use App\Http\Controllers\SizeMeasurementController;
use App\Http\Controllers\TlsPolicyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::resource('domains', DomainController::class);
Route::resource('aliases', AliasController::class);
Route::resource('tls-policies', TlsPolicyController::class);
Route::resource('mailboxes', MailboxController::class);

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('change-password', [ChangePasswordController::class, 'showPasswordChangeForm'])->name('password.change');
Route::post('change-password', [ChangePasswordController::class, 'updatePassword']);

Route::get('domains/{domain}/sizes', [SizeMeasurementController::class, 'indexForDomain'])->name('domains.sizes');
Route::delete('domains/{domain}/sizes', [SizeMeasurementController::class, 'destroyForDomain']);

Route::get('mailboxes/{mailbox}/sizes', [SizeMeasurementController::class, 'indexForMailbox'])->name('mailboxes.sizes');
Route::delete('mailboxes/{mailbox}/sizes', [SizeMeasurementController::class, 'destroyForMailbox']);

/**
 * Authentication Routes
 */

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('logout', [LoginController::class, 'logout']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset']);
