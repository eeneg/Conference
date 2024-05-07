<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@base.com',
            'password' => Hash::make('12345678')
        ]);

        // \App\Models\User::factory()->create([
        //     'name' => 'test1',
        //     'email' => 'test1@example.com',
        //     'password' => Hash::make('zxczxczxc')
        // ]);

        \App\Models\Role::create([
            'title' => 'administrator'
        ]);

        \App\Models\Role::create([
            'title' => 'user'
        ]);

        \App\Models\Storage::create([
            'title' => 'Cabinet 1',
            'location' => 'Please Edit Location',
            'details' => 'Please Edit Details'
        ]);

        // \App\Models\Storage::create([
        //     'title' => 'Sample Storage Title 1',
        //     'location' => 'Sample Storage Location 1',
        //     'details' => 'Sample Storage Details 1'
        // ]);

        \App\Models\Category::create([
            'title' => 'Ordinance',
            'type' => '1',
            'details' => 'Ordinance Files (Edit as you see fit)'
        ]);

        // \App\Models\Category::factory()->count(10)->create();

        \App\Models\Category::create([
            'title' => 'Resolutions',
            'type' => '1',
            'details' => 'Resolution Files (Edit as you see fit)'
        ]);

        // \App\Models\Category::create([
        //     'title' => 'Ordinances',
        //     'type' => '1',
        //     'details' => 'Sample Ordinance Details'
        // ]);

        \App\Models\Category::create([
            'title' => 'DILG Public Opinions',
            'type' => '2',
            'details' => 'DILG Public Opinion Files (Edit as you see fit)'
        ]);

        \App\Models\Role::create([
            'title' => 'board member'
        ]);

        User::where('email', 'admin@base.com')->first()->roles()->attach(Role::where('title', 'administrator')->first()->id);
        // User::where('email', 'test1@example.com')->first()->roles()->attach(Role::where('title', 'administrator')->first()->id);


        // \App\Models\User::factory()->count(90)->hasAttached(\App\Models\Role::where('title', 'user')->first())->create();
        // \App\Models\User::factory()->count(10)->hasAttached(\App\Models\Role::where('title', 'board member')->first())->create();
    }
}
