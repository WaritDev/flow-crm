<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('sales.user.{userId}', function ($user, int $userId) {
    return (int) $user->id === $userId;
});

