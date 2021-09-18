<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use App\{User};

Broadcast::channel('App.User.{id}', function ($user, $id) {
	// Log::debug('CHANNELS');
	// Log::debug('App.User.{id}');
	// Log::debug(print_r($user, true));
    return (int) $user->id === (int) $id;
});


Broadcast::channel('contracts', function ($user) {
	// Log::debug('CHANNELS');
	// Log::debug('contracts');
	// Log::debug(print_r($user, true));
    return $user;
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return $user->id === User::find($userId)->id;
});

