<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Laravel\Scout\Searchable;
use App\Models\PdfContent;
use App\Models\Storage;

class Attachment extends Model
{
    use HasFactory, HasUuids, Searchable;

    protected $fillable = ['conference_id', 'file_id', 'category', 'category_order', 'file_order'];

    public function conference() : BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    public function pdfContent() : MorphOne
    {
        return $this->morphOne(PdfContent::class, 'contentable');
    }

    public function storage(){
        return $this->belongsTo(Storage::class, 'storage_id');
    }

    public function file(){
        return $this->belongsTo(File::class);
    }


}
