<?php

namespace App\Actions;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class SaveAttachments
{
    public function __invoke($attachments, $title, $conference): Attachment
    {
        $sluggedTitle = str($title)->slug();

        $attachments = collect($attachments)->map(function($file) use ($sluggedTitle){
            @[$category, $files] = $file;

            if (empty($files)) {
                return [
                    'category' => $category,
                    'files' => [],
                ];
            }

            $sluggedCategory = str($category)->slug();

            return [
                'category' => $category,
                'files' => collect($files)
                    ->filter(fn ($file) => is_file($file))
                    ->map(fn ($file) => ['path' => substr(Storage::putFile("public/attachments/$sluggedTitle/$sluggedCategory", $file), 7), 'fileName' => $file->getClientOriginalName()])
                    ->toArray()
            ];
        });

        return Attachment::create([
            'conference_id' => $conference,
            'files' => $attachments->toArray()
        ]);
    }
}
