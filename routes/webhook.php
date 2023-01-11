<?php

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Webhook routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "webhook" middleware group. Enjoy building your Webhook handlers !
|
*/

Route::post('dwolla', 'WebhookController@dwolla')->name('webhook.dwolla');
Route::post('dwolla-89b4e87b80c4a35cfc2c9d5287af3e73b66f7cc447a64313cc9aea0e9b7534dd', 'WebhookController@dwollaTransfer')->name('webhook.dwolla.transfer');
Route::post('dwolla-test', 'WebhookTestController@dwolla')->name('webhook.test.dwolla.transfer');
Route::post('stripe-transfer', 'WebhookController@stripeAchTransfer')->name('webhook.stripe.transfer');
Route::post('stripe-connect', 'WebhookController@stripeConnect')->name('webhook.stripe.connect');
Route::post('usag/v{version}', 'WebhookController@usag');
Route::post('usag/test', 'WebhookController@test');