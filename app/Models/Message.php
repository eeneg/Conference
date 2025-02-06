<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Message extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['sender_id', 'message', 'read'];

    public function chats(){
        return $this->belongsTo(Chat::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient() : HasOneThrough
    {
        return $this->hasOneThrough(User::class, UserChat::class, 'messages.id', 'users.id', null, 'user_chats.user_id')
            ->join('messages', 'messages.chat_id', '=', 'user_chats.chat_id')
            ->whereNot('user_chats.user_id', auth()->id());
    }
}
