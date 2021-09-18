/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.baseURL = '/api/v1/';
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['ACCEPT']           = 'application/json';
window.axios.defaults.headers.common['Content-Type']     = 'application/json';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo'

console.log('process.env.MIX_PUSHER_APP_KEY');
console.log(process.env.MIX_PUSHER_APP_KEY);
console.log('window.location.hostname');
console.log(window.location.hostname);


window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    encrypted: false,
    // encrypted: true,
    wsHost: window.location.hostname,
    wsPort: process.env.MIX_PUSHER_APP_WS_PORT,
    wssPort: process.env.MIX_PUSHER_APP_WSS_PORT,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    // enabledTransports: ['ws'],
});
