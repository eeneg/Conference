<?php

namespace Database\Factories;

use App\Models\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $storage_id = Storage::first()->id;

        return [
            'title' => Fake()->title(),
            'file_name' => Fake()->name(),
            'storage_id' => $storage_id,
            'hash_name' => Fake()->md5(),
            'details' => Fake()->paragraph(),
            'date' => Fake()->date(),
            'latest' => true,
            'for_review' => false,
            'processed' => true,
            'sorted' => false
        ];
    }
}
