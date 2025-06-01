<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         User::factory(10)->create();
         User::factory()->create([
             'first_name' => 'Mohammad',
             'last_name' => 'Maleki',
             'email' => 'molkan99@gmail.com'
         ]);

         Article::factory(50)->create();



    }
}
