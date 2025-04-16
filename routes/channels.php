<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return Chat::whereHas('members', function ($query) use ($user, $chatId) {
        $query->where('user_id', $user->id)->where('chat_id', $chatId);
    })->exists();
});