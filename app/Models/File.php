<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Storage;
use App\Models\PdfContent;
use App\Models\Category;
use App\Models\FileCategory;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class File extends Model
{
    use HasFactory, HasUuids, Searchable;

    protected $fillable = ['title', 'file_name', 'storage_id', 'category_id', 'hash_name', 'details', 'date', 'latest', 'for_review', 'processed', 'sorted'];

    protected $casts = ['tags' => 'array'];

    public function thumbnail() : HasOne
    {
        return $this->hasOne(FileThumbnail::class);
    }

    public function storage() : BelongsTo
    {
        return $this->belongsTo(Storage::class);
    }

    public function attachment() : HasOne
    {
        return $this->hasOne(Attachment::class);
    }

    public function category() : BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'file_categories', 'file_id', 'category_id')->using(FileCategory::class);
    }

    public function pdfContent() : HasOne
    {
        return $this->hasOne(PdfContent::class);
    }

    public function fileComment() : HasMany{
        return $this->hasMany(FileComment::class);
    }

    public function fileError() : HasOne
    {
        return $this->hasOne(FileReadError::class);
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
            'content' => $this->pdfContent->content ?? '',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
