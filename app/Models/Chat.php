<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['title', 'private'];

    public function user(){
        return $this->belongsToMany(User::class);
    }

    public function messages(){
        return $this->hasMany(Message::class);
    }

    public function latestMessage(){
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function userChats()
    {
        return $this->hasMany(UserChat::class);
    }

}
