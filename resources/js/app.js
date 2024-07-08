/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import Echo from 'laravel-echo';

window.io = require('socket.io-client');
var socket_url = '';
if(window.server_env == 'local'){
    socket_url = window.location.hostname + ':6001';
}
else{
    socket_url = window.location.hostname;
}

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: socket_url,// this is laravel-echo-server host
    authEndPoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    },
});
// window.Vue = require('vue');


// const app = new Vue({
//     el: '#app',
// });

