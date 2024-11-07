<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileThumbnail extends Model
{

    use HasUuids;

    protected $fillable = ['base64_thumbnail'];

    public function file() : BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
