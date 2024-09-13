<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Reference;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['title', 'details', 'type'];

    protected $hidden = ['pivot'];

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class);
    }

    public function reference(): HasMany
    {
        return $this->hasMany(Reference::class);
    }
}
