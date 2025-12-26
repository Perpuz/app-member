<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Category;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        
        if ($categories->count() === 0) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        // Create 25 dummy books
        for ($i = 0; $i < 25; $i++) {
            $totalCopies = rand(1, 5);
            $availableCopies = rand(0, $totalCopies);
            
            Book::create([
                'title' => fake()->sentence(3),
                'author' => fake()->name(),
                'publisher' => fake()->company(),
                'publication_year' => fake()->year(),
                'isbn' => fake()->isbn13(),
                'category_id' => $categories->random()->id,
                'description' => fake()->paragraph(),
                'total_copies' => $totalCopies,
                'available_copies' => $availableCopies,
                'external_id' => 'EXT-' . ($i + 1), // Simulating external ID
            ]);
        }
    }
}
