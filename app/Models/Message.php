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
        return
            (
                $this->hasOneThrough(User::class, Message::class, 'users.id', 'messages.sender_id', null, 'messages.sender_id')
                ->join('user_chats', 'user_chats.chat_id', '=', 'messages.chat_id')

            )
            ;
    }
}
