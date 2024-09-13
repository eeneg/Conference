<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileReadError extends Model
{

    use HasUuids;

    protected $fillable = ['verbose', 'remark'];

    public function file() : BelongsTo{
        return $this->belongsTo(File::class);
    }
}
