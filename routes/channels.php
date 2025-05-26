<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('chat', function ($user) {
    return Auth::check();
});
Broadcast::channel('typing', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});

