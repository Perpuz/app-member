<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Fiksi' => 'Novel, Cerpen, dan karya imajinatif lainnya',
            'Non-Fiksi' => 'Buku berdasarkan fakta dan realita',
            'Teknologi' => 'Komputer, Programming, dan Gadget',
            'Sains' => 'Fisika, Kimia, Biologi, dan Astronomi',
            'Sejarah' => 'Sejarah dunia dan lokal',
            'Bisnis' => 'Manajemen, Keuangan, dan Entrepreneurship',
            'Self-Development' => 'Pengembangan diri dan motivasi'
        ];

        foreach ($categories as $name => $desc) {
            Category::create([
                'name' => $name,
                'description' => $desc
            ]);
        }
    }
}
