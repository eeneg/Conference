<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Ramsey\Uuid\Uuid;
use App\Models\Message;
use App\Models\Note;
use Illuminate\Contracts\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = [
        'roles'
    ];

    /**
     * The roles that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')->using(UserRole::class);
    }

    public function note(): HasOne
    {
        return $this->hasOne(Note::class);
    }

    public function chat()
    {
        return $this->belongsToMany(Chat::class, 'user_chat', 'user_id', 'chat_id')->using(UserChat::class);
    }

    // public function latestMessage()
    // {
    //     return $this->hasOne(Message::class)
    //         ->ofMany([
    //             'created_at' => 'max',
    //         ], function (Builder $query) {
    //             $query->where('messages.sender_id', '=', auth()->user()->id)
    //                 ->orWhere('messages.receiver_id', '=', auth()->user()->id);
    //         });

    //     // return $this->hasOne(Message::class)->latestOfMany();
    // }


    public function toSearchableArray(): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->roles()->first() ? $this->roles()->first()->title : ''
        ];
    }

}
