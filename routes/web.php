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

use Illuminate\Http\Response;

Auth::routes(['verify' => true]);

//Route::get('/test', function () {
////    Mail::raw('Hi, welcome user!', function ($message) {
////        $message->to('vishalinfyom@gmail.com')
////            ->subject('Test Email');
////    });
//});

Route::middleware(['guest'])->group(function () {
    Route::get('/register/member/{token}', 'Auth\RegisterController@showRegistrationForm')->name('register.member.invite');
});

Route::middleware(['auth', 'verified','checkUserActive'])->group(function () {
    Route::get('/', 'DashboardController@index')->name('dashboard');
    Route::get('/meets', 'DashboardController@browseMeets')->name('meets.browse');

    Route::get('export-sanction-levels', 'DashboardController@sanctionLevelExportExcel');

    Route::get('export-gyms', 'DashboardController@gymExportExcel');

    // conversation routes
    Route::get('/gyms/{gym}/add-meet-register-user', 'ConversationController@displayMeetRegisterUser');
    Route::get('/gyms/{gym}/add-conversion/{user}/user', 'ConversationController@addConversationUser');
    Route::post('/gyms/{gym}/send-message', 'ConversationController@sendMessage');
    Route::get('/gyms/{gym}/conversation', 'ConversationController@index')->name('gyms.conversation');
    Route::get('/gyms/{gym}/get-unread-count', 'ConversationController@unreadCount')->name('conversation.unread.count');
    Route::post('/gyms/{gym}/read-at/{receiverGymId}', 'ConversationController@realTimeReadAt');

    Route::patch('/managed/switch', 'UserAccountController@switchManagedUser')->name('managed.switch');

    Route::get('/invite/member/{token}/accept', 'UserAccountController@acceptInvite')->name('invite.member.accept');
    Route::delete('/invite/member/{id}/remove', 'UserAccountController@removeInvite')->name('invite.member.remove');
    Route::post('/invite/member/{id}/resent', 'UserAccountController@resendInvite')->name('invite.member.resend');

    Route::get('/account/profile', 'UserAccountController@showProfile')->name('account.profile');

    Route::get('/account/stripebank', 'UserAccountController@stripebank')->name('account.stripebank');
    Route::post('/account/stripebank/add', 'UserAccountController@stripebankadd')->name('account.stripe.bank.add');
    Route::post('/account/stripebank/verify', 'UserAccountController@stripebankverify')->name('account.stripe.bank.verify');

    Route::get('/account/payment_options', 'UserAccountController@showPaymentOptions')->name('account.payment.options');
    Route::get('/account/access_management', 'UserAccountController@showAccessManagement')->name('account.access.management');
    Route::get('/account/balance_transactions', 'UserAccountController@showBalanceTransactions')->name('account.balance.transactions');
    Route::get('/account/schedule_withdraw', 'UserAccountController@schedule_withdraw')->name('account.balance.schedule_withdraw');
    Route::post('/account/init_schedule', 'UserAccountController@initiate_withdraw_schedule')->name('account.withdraw.initiate');
    Route::post('/account/change_status', 'UserAccountController@toogleWithSchedule')->name('account.withdraw.change_status');
    
    // Route::get('/account/test', 'UserAccountController@testT')->name('account.withdraw.initiate');
    Route::get('/account/dwolla_verify', 'UserAccountController@showDwollaVerificationPage')->name('account.dwolla.verify');

    Route::post('/account/dwolla_verify', 'UserAccountController@dwollaVerifyInfo')->name('account.dwolla.verify.info');
    Route::post('/account/dwolla_document', 'UserAccountController@dwollaUploadDocument')->name('account.dwolla.verify.document');

    Route::patch('/account', 'UserAccountController@profile')->name('account.profile.update');
    Route::get('/account/site/{id}', 'UserAccountController@showProfileSite')->name('account.showProfileSite'); // trackthis

    Route::post('/account/picture/reset', 'UserAccountController@clearProfilePicture')->name('account.picture.reset');
    Route::post('/account/picture/upload', 'UserAccountController@changeProfilePicture')->name('account.picture.change');
    Route::patch('/account/password/reset', 'UserAccountController@resetPassword')->name('account.password.reset');

    Route::post('/account/card', 'UserAccountController@storeCard')->name('account.card.add');
    Route::delete('/account/card/{id}', 'UserAccountController@deleteCard')->name('account.card.remove');

    Route::delete('/account/bank/{id}', 'UserAccountController@deleteBankAccount')->name('account.bank.remove');
    Route::patch('/account/bank/verify', 'UserAccountController@verifyMicroDeposits')->name('account.bank.verify');

    Route::post('/account/members', 'UserAccountController@inviteMember')->name('account.member.invite');
    Route::patch('/account/member', 'UserAccountController@changeMemberPermissions')->name('account.member.permissions.edit');
    Route::delete('/account/member/{id}', 'UserAccountController@removeMember')->name('account.member.remove');

    Route::delete('/account/managed/{id}', 'UserAccountController@removeManagedAccount')->name('account.managed.remove');

    Route::middleware(['permission:manage_gym'])->group(function () {
        Route::patch('/gyms/{gym}/restore', 'GymController@restore')->name('gyms.restore');
        Route::post('/gyms/{gym}/picture/reset', 'GymController@clearProfilePicture')->name('gyms.picture.reset');
        Route::post('/gyms/{gym}/picture/upload', 'GymController@changeProfilePicture')->name('gyms.picture.change');
        Route::get('/gyms/{gym}/registration/{registration}', 'MeetRegistrationController@show')->name('gyms.registration');
        Route::resource('gyms', 'GymController');
    });

    Route::middleware(['permission:manage_roster'])->group(function () {
        Route::resource('gyms.athletes', 'AthleteController')->except(['destroy']);
        Route::delete('/gyms/{gym}/athletes/batch/delete', 'AthleteController@batchRemove')->name('gyms.athletes.batch.delete');

        Route::post('/gyms/{gym}/athletes/import', 'AthleteController@import')->name('gyms.athletes.import');
        Route::post('/gyms/{gym}/athletes/import/failed/{failed}/', 'AthleteController@storeFromFailedImport')->name('gyms.athletes.import.failed.create');
        Route::get('/gyms/{gym}/athletes/import/failed/{failed}/edit', 'AthleteController@failedImportEdit')->name('gyms.athletes.import.failed.edit');
        Route::patch('/gyms/{gym}/athletes/import/failed/{failed}/{duplicate}', 'AthleteController@failedImportUpdate')->name('gyms.athletes.import.failed.update');
        Route::delete('/gyms/{gym}/athletes/imports/failed/batch/delete', 'AthleteController@batchRemoveFailed')->name('gyms.athletes.failed.import.batch.delete');

        Route::resource('gyms.coaches', 'CoachController')->except(['destroy']);

        Route::delete('/gyms/{gym}/coaches/batch/delete', 'CoachController@batchRemove')->name('gyms.coaches.batch.delete');

        Route::post('/gyms/{gym}/coaches/import', 'CoachController@import')->name('gyms.coaches.import');
        Route::post('/gyms/{gym}/coaches/import/failed/{failed}/', 'CoachController@storeFromFailedImport')->name('gyms.coaches.import.failed.create');
        Route::get('/gyms/{gym}/coaches/import/failed/{failed}/edit', 'CoachController@failedImportEdit')->name('gyms.coaches.import.failed.edit');
        Route::patch('/gyms/{gym}/coaches/import/failed/{failed}/{duplicate}', 'CoachController@failedImportUpdate')->name('gyms.coaches.import.failed.update');
        Route::delete('/gyms/{gym}/coaches/imports/failed/batch/delete', 'CoachController@batchRemoveFailed')->name('gyms.coaches.failed.import.batch.delete');
    });

    Route::middleware(['permission:create_meet'])->group(function () {
        Route::get('/gyms/{gym}/meets/create', 'MeetController@create')->name('gyms.meets.create');
        Route::get('/gyms/{gym}/meets/create/scratch', 'MeetController@createFromScratch')->name('gyms.meets.create.scratch');
        Route::post('/gyms/{gym}/meets/create/copy', 'MeetController@createFromCopy')->name('gyms.meets.create.copy');
        Route::get('/gyms/{gym}/meets/create/{step}/entry/{temporary}', 'MeetController@stepView')->name('gyms.meets.create.step.view');
        Route::post('/gyms/{gym}/meets/store/1/entry/{temporary}', 'MeetController@storeStepOne')->name('gyms.meets.store.1');
        Route::post('/gyms/{gym}/meets/store/2/entry/{temporary}', 'MeetController@storeStepTwo')->name('gyms.meets.store.2');
        Route::post('/gyms/{gym}/meets/store/3/entry/{temporary}', 'MeetController@storeStepThree')->name('gyms.meets.store.3');
        Route::post('/gyms/{gym}/meets/store/4/entry/{temporary}', 'MeetController@storeStepFour')->name('gyms.meets.store.4');
        Route::post('/gyms/{gym}/meets/store/5/entry/{temporary}', 'MeetController@storeStepFive')->name('gyms.meets.store.5');
    });

    Route::get('/meets/{meet}', 'MeetController@details')->name('gyms.meets.details');

    Route::middleware(['permission:create_meet,edit_meet'])->group(function () {
        Route::get('/gyms/{gym}/meets', 'MeetController@index')->name('gyms.meets.index');

        Route::get('/gyms/{gym}/sanctions/usag/{sanction}', 'USAGSanctionController@index')->name('gyms.sanctions.usag');
        Route::get('/gyms/{gym}/sanctions/usag/{sanction}/reservation', 'USAGReservationController@index')->name('gyms.reservation.usag');
    });

    Route::middleware(['permission:edit_meet'])->group(function () {
        Route::get('/gyms/{gym}/meets/{meet}/edit/{step?}', 'MeetController@edit')->name('gyms.meets.edit');

        Route::patch('/gyms/{gym}/meets/{meet}/update/1', 'MeetController@updateStepOne')->name('gyms.meets.update.1');
        Route::patch('/gyms/{gym}/meets/{meet}/update/2', 'MeetController@updateStepTwo')->name('gyms.meets.update.2');
        Route::patch('/gyms/{gym}/meets/{meet}/update/3', 'MeetController@updateStepThree')->name('gyms.meets.update.3');
        Route::patch('/gyms/{gym}/meets/{meet}/update/4', 'MeetController@updateStepFour')->name('gyms.meets.update.4');
        Route::patch('/gyms/{gym}/meets/{meet}/update/5', 'MeetController@updateStepFive')->name('gyms.meets.update.5');

        Route::post('/gyms/{gym}/meets/{meet}/archive', 'MeetController@archive')->name('gyms.meets.archive');
        Route::post('/gyms/{gym}/meets/{meet}/restore', 'MeetController@restore')->name('gyms.meets.restore');
        

        Route::post('/gyms/{gym}/meets/{meet}/picture/reset', 'MeetController@clearProfilePicture')->name('gyms.meets.picture.reset');
        Route::post('/gyms/{gym}/meets/{meet}/picture/upload', 'MeetController@changeProfilePicture')->name('gyms.meets.picture.change');

        Route::post('/gyms/{gym}/meets/{meet}/publish', 'MeetController@publish')->name('gyms.meets.publish');
        Route::post('/gyms/{gym}/meets/{meet}/unpublish', 'MeetController@unpublish')->name('gyms.meets.unpublish');

        Route::post('/send-mail/past-meets', 'MeetController@sendMailToPastMeets');
    });

    Route::middleware(['permission:register'])->group(function () {
        Route::get('/meets/{meet}/register', 'MeetRegistrationController@index')->name('gyms.meets.register');
        Route::post('/gym/{gym}/registration/{registration}/remaining-payment', 'MeetRegistrationController@remainingPayment')->name('gyms.registration.remaining.payment');
        Route::get('/gym/{gym}/registration/{registration}/pay/{transaction}', 'MeetRegistrationController@pay')->name('gyms.registration.pay');
        Route::get('/gyms/{gym}/joined', 'GymController@joinedMeets')->name('gyms.meets.joined');
        Route::get('/gym/{gym}/registration/{registration}/edit', 'MeetRegistrationController@edit')->name('gyms.registration.edit');
    });

    Route::middleware(['permission:access_report'])->group(function () {
        Route::middleware('throttle:50,1')->get('/gym/{gym}/registration/{registration}/report/{reportType}', 'MeetRegistrationController@reportCreate');

        Route::middleware('throttle:5,1')->get('/host/{hostingGym}/meets/{meet}/report/{reportType}/create/{gym?}', 'MeetController@hostReportCreate');
        Route::get('/host/{gym}/meets/{meet}/dashboard', 'MeetController@hostMeetDashboard')->name('host.meets.dashboard');

        //meet dashboard chart route
        Route::get('meets/{meet}/line-chart-meet', 'MeetController@getLineChartData');
        Route::get('meets/{meet}/bar-chart-meet', 'MeetController@getBarChartData');
        Route::get('meets/{meet}/pie-chart-meet', 'MeetController@getPieChartData');

        //meet dashboard summary report
        Route::get('meets/{meet}/athlete-summary','MeetController@getAthleteSummaryReport')->name('athlete.summary-report');
        Route::get('meets/{meet}/coach-summary','MeetController@getCoachSummaryReport')->name('coach.summary-report');
        Route::get('meets/{meet}/gym-summary','MeetController@getGymSummaryReport')->name('gym.summary-report');

        Route::post('send-mass-notification','MeetController@sendMassNotification')->name('send-mass-notification');
    });

    Route::group(['middleware' => ['checkIsAdmin'], 'prefix' => 'admin','namespace'  => 'Admin'], function () {
        Route::get('meets', 'MeetController@index')->name('admin.meets');
        Route::get('featured-meets', 'MeetController@featuredMeets')->name('admin.featured.meets');
        Route::post('meets/{meet}/meet-featured', 'MeetController@meetFeatured')->name('admin.meet.featured');
        Route::get('meet-gyms', 'MeetController@getMeetGyms')->name('admin.meet.gyms');
        Route::get('meets/{meet}/dashboard', 'MeetController@meetDashboard')->name('admin.meet.dashboard');
        Route::post('meets/{meet}/update-custom-handling-fee', 'MeetController@updateHandlingFee')->name('update.handling-fee');

        Route::get('custom/users', 'UserController@customUsers')->name('admin.cusers');
        
        Route::get('users', 'UserController@index')->name('admin.users');
        Route::post('users/{user}/send-reset-email','UserController@senResetEmail')->name('admin-user.send.reset-email');
        Route::post('users/{user}/active-deactive','UserController@activeDeactivateUser')->name('active-deactive.user');
        Route::post('users/{user}/withdrawal-freeze','UserController@userWithdrawalFreeze')->name('withdrawal-freeze.user');
        Route::post('users/{user}/withdrawal-money','UserController@userWithdrawalMoney')->name('withdrawal.money.user');
        Route::post('users/{user}/mail-check-option','UserController@userMailCheckOption')->name('mail-check-option.user');
        Route::get('users/{user}/edit','UserController@edit')->name('users.edit');
        Route::patch('users/{user}/update','UserController@update')->name('users.update');

        //transfer routes
        Route::get('transfer','TransferController@transfer')->name('admin.transfer');
        Route::get('create/transfer','TransferController@createTransfer')->name('create.transfer');
        Route::post('transfer','TransferController@storeTransfer')->name('store.transfer');

        //transfer routes
        Route::get('transfer','TransferController@transfer')->name('admin.transfer');
        Route::get('transfer/payments','TransferController@createTransfer')->name('create.transfer');
        Route::post('transfer/store','TransferController@storeTransfer')->name('store.transfer');
        Route::post('transfer/bank-accounts','TransferController@getUserBankAccounts')->name('user.bank.accounts');

        Route::get('users/users-export-excel','UserController@userExportExcel')->name('users.export.excel');
        Route::get('users/users-export-pdf','UserController@userExportPDF')->name('users.export.pdf');

        Route::get('meets/meets-export-excel','MeetController@meetExportExcel')->name('meets.export.excel');
        Route::get('meets/meets-export-pdf','MeetController@meetExportPDF')->name('meets.export.pdf');

        Route::get('settings', 'SettingController@index')->name('admin.settings');
        Route::post('settings','SettingController@update')->name('admin.settings.update');

        Route::get('dashboard','DashboardController@index')->name('admin.dashboard');

        Route::get('reports-gym-balance','GymBalanceReportController@index')->name('admin.gym.balance.reports');

        Route::get('usag-reservations','USAGReservationController@index')->name('admin.usag.reservations');
        Route::delete('usag-reservations/{id}', 'USAGReservationController@usagReservationDestroy')->name('admin.usag.reservations.destroy');
        Route::post('usag-reservations/{id}/usag-reservation-hide','USAGReservationController@usagReservationHide')->name('admin.usag.reservations.hide');
        Route::get('search-usag-reservations', 'USAGReservationController@searchUsagReservation')->name('search.usag.reservation');

        Route::get('usag-sanctions','USAGSanctionController@index')->name('admin.usag.sanctions');
        Route::delete('usag-sanctions/{id}', 'USAGSanctionController@usagSanctionDestroy')->name('admin.usag.reservations.destroy');
        Route::post('usag-sanctions/{id}/usag-sanctions-hide','USAGSanctionController@usagSanctionHide')->name('admin.usag.reservations.hide');
        Route::get('search-usag-sanctions', 'USAGSanctionController@searchUsagSanction')->name('search.usag.sanction');

        Route::get('pending-withdrawal-balance-report','DashboardController@pendingWithdrawalBalance')->name('pending.withdrawal.balance.report');
        Route::get('print-pending-withdrawal-balance-report','DashboardController@printPendingWithdrawalBalance')->name('print.pending.withdrawal.balance');
    });

    Route::get('/impersonate/{userId}', 'UserAccountController@impersonate')->name('impersonate');
    Route::get('/impersonate-leave','UserAccountController@impersonateLeave')->name('impersonate.leave');

    Route::get('/meet/{meet}/gym/{gym}/print-check-sending-details', 'MeetController@printCheckSendingDetails');
});