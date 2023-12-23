const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/auth/auth.js', 'public/js/auth')
    .js('resources/js/main.js', 'public/js/')
    .js('resources/js/browse.js', 'public/js/')

    .js('resources/js/include/sanction-notifications.js', 'public/js/include/')
    .js('resources/js/include/chat-conversion.js', 'public/js/include/')

    .js('resources/js/user/account-profile.js', 'public/js/user')
    .js('resources/js/user/account-payment-options.js', 'public/js/user')
    .js('resources/js/user/account-access-management.js', 'public/js/user')
    .js('resources/js/user/account-balance-transactions.js', 'public/js/user')

    .js('resources/js/user/account-dwolla-verify.js', 'public/js/user')

    .js('resources/js/gym/gyms.js', 'public/js/gym')
    .js('resources/js/gym/gym-create.js', 'public/js/gym')
    .js('resources/js/gym/gym-edit.js', 'public/js/gym')
    .js('resources/js/gym/joined.js', 'public/js/gym')

    .js('resources/js/athlete/athletes.js', 'public/js/athlete')
    .js('resources/js/athlete/athlete-create.js', 'public/js/athlete')
    .js('resources/js/athlete/athlete-edit.js', 'public/js/athlete')

    .js('resources/js/coach/coaches.js', 'public/js/coach')
    .js('resources/js/coach/coach-create.js', 'public/js/coach')
    .js('resources/js/coach/coach-edit.js', 'public/js/coach')

    .js('resources/js/meet/meets.js', 'public/js/meet')
    .js('resources/js/meet/meet-details.js', 'public/js/meet')
    .js('resources/js/meet/meet-create-copy.js', 'public/js/meet')
    .js('resources/js/meet/create/meet-create-1.js', 'public/js/meet/create')
    .js('resources/js/meet/create/meet-create-2.js', 'public/js/meet/create')
    .js('resources/js/meet/create/meet-create-3.js', 'public/js/meet/create')
    .js('resources/js/meet/create/meet-create-4.js', 'public/js/meet/create')
    .js('resources/js/meet/create/meet-create-5.js', 'public/js/meet/create')
    .js('resources/js/meet/meet-edit.js', 'public/js/meet')

    .js('resources/js/host/meet/dashboard.js', 'public/js/host/meet')
    .js('resources/js/host/meet/meet-dashboard.js', 'public/js/host/meet')
    .js('resources/js/host/meet/meet_summary.js', 'public/js/host/meet')


    .js('resources/js/register/register.js', 'public/js/register')
    .js('resources/js/register/edit.js', 'public/js/register')
    .js('resources/js/register/details.js', 'public/js/register')
    .js('resources/js/register/repay.js', 'public/js/register')

    .js('resources/js/usag/details.js', 'public/js/usag')

    // JS Includes
    .js('resources/js/include/modals/contact_us.js', 'public/js/include/modals')
    .js('resources/js/include/nav/sidebar.js', 'public/js/include/nav')
    .js('resources/js/include/nav/topbar.js', 'public/js/include/nav')

    // CSS
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/auth.scss', 'public/css')
    .sass('resources/sass/main.scss', 'public/css')
    .sass('resources/assets/admin/style/style.scss','public/assets/admin/style/style.css')
    .sass('resources/assets/user/style/style.scss','public/assets/user/style/style.css')
    .sass('resources/assets/admin/style/dashboard.scss','public/assets/admin/style/dashboard.css')
    .sass('resources/assets/admin/style/usag.scss','public/assets/admin/style/usag.css')

    //Admin
    .js('resources/assets/admin/js/custom.js', 'public/assets/admin/js/custom.js')
    .js('resources/assets/admin/js/custom-datatable.js', 'public/assets/admin/js/custom-datatable.js')
    .js('resources/assets/admin/js/meet/meet.js', 'public/assets/admin/js/meet/meet.js')
    .js('resources/assets/admin/js/meet/dashboard.js', 'public/assets/admin/js/meet/dashboard.js')
    .js('resources/assets/admin/js/user/user.js', 'public/assets/admin/js/user/user.js')
    .js('resources/assets/admin/js/setting/setting.js', 'public/assets/admin/js/setting/setting.js')
    .js('resources/assets/admin/js/reports/reports.js', 'public/assets/admin/js/reports/reports.js')
    .js('resources/assets/admin/js/usag_reservation/usag_reservation.js', 'public/assets/admin/js/usag_reservation/usag_reservation.js')
    .js('resources/assets/admin/js/usag_sanction/usag_sanction.js', 'public/assets/admin/js/usag_sanction/usag_sanction.js')
    .js('resources/assets/admin/js/featured_meets/feature_meets.js', 'public/assets/admin/js/featured_meets/feature_meets.js')
    .js('resources/assets/admin/js/transfer/transfer.js', 'public/assets/admin/js/transfer/transfer.js')
    .js('resources/assets/admin/js/transfer/create-edit.js', 'public/assets/admin/js/transfer/create-edit.js')
    .version();

mix.copyDirectory('node_modules/summernote/dist/font',
    'public/assets/admin/css/font');

//Copy CSS
mix.copy('node_modules/bootstrap/dist/css/bootstrap.min.css',
    'public/assets/admin/css/bootstrap.min.css');
mix.copy('node_modules/admin-lte/dist/css/adminlte.min.css',
    'public/assets/admin/css/adminlte.css');
mix.copy('node_modules/datatables.net-dt/css/jquery.dataTables.min.css',
    'public/assets/admin/css/jquery.dataTables.min.css')
mix.copy('node_modules/datatables.net-dt/images', 'public/assets/admin/images');
mix.copy('node_modules/izitoast/dist/css/iziToast.min.css',
    'public/assets/admin/css/iziToast.min.css');
mix.copy('node_modules/select2/dist/css/select2.min.css',
    'public/assets/admin/css/select2.min.css');
mix.copy('node_modules/summernote/dist/summernote.min.css',
    'public/assets/admin/css/summernote.min.css');
mix.copy('node_modules/sweetalert/dist/sweetalert.css',
    'public/assets/admin/css/sweetalert.css');

//Copy JS
mix.babel('node_modules/jquery/dist/jquery.min.js',
    'public/assets/admin/js/jquery.min.js');
mix.babel('node_modules/popper.js/dist/umd/popper.min.js',
    'public/assets/admin/js/popper.min.js');
mix.babel('node_modules/bootstrap/dist/js/bootstrap.min.js',
    'public/assets/admin/js/bootstrap.min.js');
mix.babel('node_modules/jquery.nicescroll/dist/jquery.nicescroll.js',
    'public/assets/admin/js/jquery.nicescroll.js');
mix.copy('node_modules/admin-lte/dist/js/adminlte.min.js',
    'public/assets/admin/js/adminlte.js');
mix.babel('node_modules/datatables.net/js/jquery.dataTables.min.js',
    'public/assets/admin/js/jquery.dataTables.min.js');
mix.babel('node_modules/moment/min/moment.min.js',
    'public/assets/admin/js/moment.min.js');
mix.babel('node_modules/izitoast/dist/js/iziToast.min.js',
    'public/assets/admin/js/iziToast.min.js');
mix.babel('node_modules/select2/dist/js/select2.min.js',
    'public/assets/admin/js/select2.min.js');
mix.babel('node_modules/summernote/dist/summernote.min.js',
    'public/assets/admin/js/summernote.min.js');
mix.babel('node_modules/chart.js/dist/Chart.min.js',
    'public/assets/admin/js/chart.min.js');
mix.babel('node_modules/sweetalert/dist/sweetalert.min.js',
    'public/assets/admin/js/sweetalert.min.js');
mix.babel('node_modules/party-js/bundle/party.min.js',
    'public/assets/admin/js/party.min.js');

        
// mix.copy('node_modules/@toast-ui/',
// 'public/assets/admin/js/toast-ui/');


mix.js('resources/js/meet/meet-calendar.js', 'public/js/meet')