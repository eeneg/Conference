<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserChat extends Model
{
    use HasUuids;
    protected $table = 'user_chats';
    protected $fillable = ['user_id', 'chat_id'];
}
