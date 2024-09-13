<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use App\Models\Category;
use App\Models\PdfContent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Laravel\Scout\Attributes\SearchUsingFullText;

class Reference extends Model
{
    use HasUuids, HasFactory, Searchable;

    protected $fillable = ['title', 'date', 'details', 'file_name', 'hash_name'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function pdfContent() : MorphOne
    {
        return $this->morphOne(PdfContent::class, 'contentable');
    }

    #[SearchUsingFullText(['details, content, category'])]
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->getKey(),
            'title' => $this->title,
            'file_name' => $this->file_name,
            'details' => $this->details,
            'date' => $this->date,
        ];
    }
}
