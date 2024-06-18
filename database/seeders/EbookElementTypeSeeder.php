<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EbookElementType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EbookElementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            ['name' => 'Heading'],
            ['name' => 'Paragraph'],
            ['name' => 'Image 1'],
            ['name' => 'Image 2'],
            ['name' => 'Image 3'],
            ['name' => 'Image 4'],
            ['name' => 'Image 5'],
            ['name' => 'Image 6'],
            ['name' => 'Image 7'],
            ['name' => 'Image 8'],
            ['name' => 'Image 10'],
            ['name' => 'List'],
            ['name' => 'Examples'],
            ['name' => 'Gif Image'],
            ['name' => 'Programming Example with Practice'],
            ['name' => 'Programming Example with Video and Practice'],
            ['name' => 'Programming Example with Image and Practice'],
            ['name' => 'Multiple Buttons'],
            ['name' => 'Text Box'],
            ['name' => 'Single Button'],
        ];

        EbookElementType::insert($types);
    }
}
