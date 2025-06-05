<?php

// use Illuminate\Support\Facades\Broadcast;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
// Broadcast::channel('chat-channel', function ($user) {
//     return $user; // Allow all authenticated users to listen to the chat channel
// });

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{room}', function ($user, $room) {
    if (!$user) {
        return false;
    }
    
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar ?? null,
    ];
});