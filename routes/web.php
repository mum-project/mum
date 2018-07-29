<?php

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

use Illuminate\Support\Facades\Route;

Route::resource('domains', 'DomainController');
Route::resource('aliases/requests', 'AliasRequestController')->names('alias-requests')->parameters(['requests'=>'alias_request']);
Route::patch('aliases/requests/{alias_request}/status', 'AliasRequestController@updateStatus')->name('alias-requests.status');
Route::resource('aliases', 'AliasController');
Route::resource('tls-policies', 'TlsPolicyController');
Route::resource('mailboxes', 'MailboxController');
Route::resource('integrations', 'IntegrationController');
Route::resource('system-services', 'SystemServiceController');

Route::get('/', 'HomeController@index')->name('home');

Route::get('change-password', 'Auth\ChangePasswordController@showPasswordChangeForm')->name('password.change');
Route::post('change-password', 'Auth\ChangePasswordController@updatePassword');

Route::get('domains/{domain}/sizes', 'SizeMeasurementController@indexForDomain')->name('domains.sizes');
Route::delete('domains/{domain}/sizes', 'SizeMeasurementController@destroyForDomain')->name('domains.sizes');

Route::get('mailboxes/{mailbox}/sizes', 'SizeMeasurementController@indexForMailbox')->name('mailboxes.sizes');
Route::delete('mailboxes/{mailbox}/sizes', 'SizeMeasurementController@destroyForMailbox')->name('mailboxes.sizes');

/**
 * Authentication Routes
 */

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LoginController@logout');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
