<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//  Login route is : vendor\laravel\framework\src\Illuminate\Foundation\Auth\AuthenticatesUsers.php
Route::middleware(['guest:api'])->group(function () {
    Route::post('login', 'UserController@login');
    Route::post('register', 'UserController@create');
});
Route::post('password/emailAPI', 'ForgotPasswordControllerApi@changedTheName');
Route::get('/test-dwolla', 'UserController@testDwolla');
Route::middleware(['auth:api'])->group(function() {
    Route::post('logout', 'UserController@logout');
});

Route::middleware(['throttle:6,1'])->group(function () {
    Route::post('contact', 'ContactController@index');
});

Route::get('levels', 'ExternalAPIController@levels');
Route::get('meets', 'ExternalAPIController@meets');
Route::get('meet/{meet}', 'ExternalAPIController@meet');
Route::get('states', 'ExternalAPIController@states');
Route::get('countries', 'ExternalAPIController@countries');
Route::get('bodies', 'ExternalAPIController@bodies');

Route::get('file-download/{meet}', 'MeetController@download')->name('file.download');

Route::get('web/meets', 'ExternalAPIController@meetsApi');
Route::get('web/meet/{meet}', 'ExternalAPIController@meetApi');

Route::post('subscribe','ExternalAPIController@meetSubscribe');
Route::post('unsubscribe','ExternalAPIController@meetUnSubscribe');
Route::get('subscribed/meets/{user?}','ExternalAPIController@subscribedMeets');

Route::prefix('ps')->group(function () {
    Route::get('meet/{meet}/{body?}', 'ExternalAPIController@ps_meet');
    Route::get('registration/{registration}/{filter?}', 'ExternalAPIController@ps_registration');
    Route::get('get-sanction-numbers', 'ExternalAPIController@getSanction');
});

Route::get('pro-score/{meet}/{sanction?}/{sanction_id?}', 'ExternalAPIController@proScoreMeet');
Route::get('athletes/{gymID}/{membershipID?}/{sanction?}', 'ExternalAPIController@proScoreMeetAthletes');
Route::get('export-sanction-levels', 'ExternalAPIController@sanctionLevelExportExcel');

Route::middleware(['auth:api', 'verified'])->group(function () {
    /*Route::get('/', function ()
    {
        return response()->json(['api' => '0.5.0']);
    });*/

    Route::get('app/withdrawal/fees', 'AppController@withdrawalFees');
    Route::get('user/dwolla/verify', 'UserController@isDwollaVerified');

    Route::get('app/levels', 'AthleteController@athleteLevelList');
    Route::get('app/bodies', 'AthleteController@bodiesList');
    Route::get('app/specialist', 'AthleteController@specialistEvents');

    Route::get('app/states', 'MeetController@statesList');

    Route::get('app/meets', 'MeetController@meets');
    Route::get('app/meet/{meet}', 'MeetController@meet');
    Route::get('app/meet/gym/participant/{meetId}', 'MeetController@participatingGyms');

    Route::get('user', 'UserController@index');
    Route::post('user', 'UserController@profile');

    Route::post('user/picture/reset', 'UserController@clearProfilePicture');
    Route::post('user/picture/change', 'UserController@changeProfilePicture');
    Route::post('user/password/reset', 'UserController@resetPassword');

    Route::get('user/cards', 'UserController@getCards');
    Route::get('user/card/{id}', 'UserController@getCard');
    Route::post('user/card/add', 'UserController@storeCard');
    Route::post('user/card/{id}/remove', 'UserController@deleteCard');

    Route::get('managed/cards', 'UserController@getCardsForManaged');

    Route::get('user/balance/transactions', 'UserController@getBalanceTransactions');

    Route::get('users/joined-meets', 'UserController@getJoinedMeets');

    Route::get('user/bank/accounts', 'UserController@getBankAccounts');
    Route::get('user/bank/account/{id}', 'UserController@getBankAccount');
    Route::get('user/bank/iav', 'UserController@getIAVToken');
    Route::post('user/bank/account/{id}/remove', 'UserController@deleteBankAccount');
    Route::post('user/bank/account/{id}/verify', 'UserController@verifyMicroDeposits');

    Route::post('user/balance/withdraw', 'UserController@withdrawBalance');

    Route::get('user/notifications','UserController@notifications');
    Route::get('user/notifications/read','UserController@readNotifications');
    Route::middleware(['permission:manage_gym'])->group(function () {
        Route::get('/user/sanctions', 'UserController@getAllGymSanctions');
        Route::get('/user/reservations', 'UserController@getAllGymReservations');

        Route::get('/registration/{registration}', 'RegistrationController@registrationDetails')->name('registration.details');
    });

    Route::get('gym-info/{gym}', 'GymController@gymInfo');
    Route::get('gyms/{gym}', 'GymController@index');
    Route::get('gyms/{gym}/athletes', 'AthleteController@athleteList');
    Route::get('gyms/{gym}/coaches', 'CoachController@coachList');

    Route::get('meets/{meet}/gyms', 'MeetController@getGymList');

    Route::middleware(['permission:manage_roster'])->group(function () {
        Route::post('gyms/{gym}/athletes/{athlete}/delete', 'AthleteController@athleteRemove');

//        Route::post('gyms/{gym}/athletes/import', 'AthleteController@athletesImports');
        Route::get('gyms/{gym}/athletes/import/faulty', 'AthleteController@failedImports');
        Route::post('gyms/{gym}/athletes/import/faulty/{faulty}/delete', 'AthleteController@faultyImportRemove');

        Route::post('gyms/{gym}/coaches/{coach}/delete', 'CoachController@coachRemove');

//        Route::post('gyms/{gym}/coaches/import', 'CoachController@coachesImports');
        Route::get('gyms/{gym}/coaches/import/faulty', 'CoachController@failedImports');
        Route::post('gyms/{gym}/coaches/import/faulty/{faulty}/delete', 'CoachController@faultyImportRemove');
    });

    Route::middleware(['permission:create_meet,edit_meet'])->group(function () {
        Route::get('gyms/{gym}/meets', 'MeetController@meetList');
        Route::get('gyms/{gym}/meets/active', 'MeetController@activeMeetList');
        Route::get('gyms/{gym}/meets/archived', 'MeetController@archivedMeetList');
        Route::post('/gyms/{gym}/reservations/usag/{sanction}/merge', 'USAGReservationController@merge');
        Route::post('/gyms/{gym}/sanctions/usag/{sanction}/merge', 'USAGSanctionController@merge');
        Route::post('/gyms/{gym}/meets/{meet}/close', 'MeetController@close'); // close meet
    });

    Route::middleware(['permission:edit_meet'])->group(function () {
        Route::post('gyms/{gym}/meets/{meet}/delete', 'MeetController@removeMeet');
    });

    Route::middleware(['permission:register'])->group(function () {
        Route::post('/registration/register/{meet}/{gym}', 'RegistrationController@register');
        Route::post('/registration/register/coupon', 'RegistrationController@checkCoupon');
        Route::post('/gym/{gym}/registration/{registration}/pay/{transaction}', 'RegistrationController@pay');
        Route::post('/gym/{gym}/registration/{registration}/edit/pay', 'RegistrationController@edit');
        Route::get('/registration/payment/options/{meet}/{gym}', 'RegistrationController@paymentOptions');
        Route::get('/gym/{gym}/joined', 'GymController@joinedMeets');
    });

    Route::middleware(['permission:access_report'])->group(function () {
        Route::get('/host/{gym}/meets/{meet}/details', 'MeetController@hostMeetDetails');
        Route::get('/host/{gym}/meets/{meet}/registration/{registration}/check/{check}/confirm/{card}', 'MeetController@hostConfirmCheck');
        Route::get('/host/{gym}/meets/{meet}/registration/{registration}/check/{check}/reject', 'MeetController@hostRejectCheck');
        Route::get('/host/{gym}/meets/{meet}/registration/{registration}/transaction/{transaction}/confirm', 'MeetController@hostConfirmWaitlistEntry');
        Route::get('/host/{gym}/meets/{meet}/registration/{registration}/transaction/{transaction}/reject', 'MeetController@hostRejectWaitlistEntry');
        Route::get('/host/{gym}/meets/{meet}/registration/{registration}/verify', 'MeetController@hostVerifyEntrants');

        Route::get('/host/{gym}/meets/{meet}/deposit', 'MeetController@createDeposit');
        Route::get('/host/{gym}/meets/{meet}/deposit/edit', 'MeetController@editDeposit');
        Route::get('/host/{gym}/meets/{meet}/deposit/disable', 'MeetController@disableDeposit');
        Route::get('/host/{gym}/meets/{meet}/deposit/enable', 'MeetController@enableDeposit');
    });
});