<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Architecture & Spaces',
                'slug' => 'architecture-spaces',
                'description' => 'Exploring monolithic structures, spatial harmonies, natural light interventions, and contextual architecture worldwide.'
            ],
            [
                'name' => 'Interior & Living',
                'slug' => 'interior-living',
                'description' => 'Curated living spaces, tactile materials, bespoke joinery, and the art of deliberate dwelling.'
            ],
            [
                'name' => 'Art & Culture',
                'slug' => 'art-culture',
                'description' => 'Sculptural expressions, site-specific installations, and cultural narratives shaping contemporary spatial philosophy.'
            ],
            [
                'name' => 'Product & Object Design',
                'slug' => 'product-object-design',
                'description' => 'Timeless industrial forms, artisanal craft objects, utilitarian aesthetics, and material research.'
            ],
            [
                'name' => 'Visual & Creative',
                'slug' => 'visual-creative',
                'description' => 'Photographic essays, architectural documentation, graphic identities, and creative design perspectives.'
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
