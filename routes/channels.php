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

// Broadcast::channel('App.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

Broadcast::channel('channel-logs.{id}', function ($user, $id) {
    if ($user->id == $id) {
        return true;
    }
    return false;
});


Broadcast::channel('channel-logs', function ($user) {
    // \Log::debug('Presence channel checks: ' . $user->role);
    if (in_array($user->role, ['superadmin', 'admin'])) {
        return true;
    }
});
