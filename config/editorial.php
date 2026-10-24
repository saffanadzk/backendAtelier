<?php

return [
    'brand_name' => env('EDITORIAL_BRAND_NAME', 'ATELIER'),
    'tagline' => env('EDITORIAL_TAGLINE', 'Journal of Architecture, Spaces & Spatial Culture'),
    'description' => env('EDITORIAL_DESCRIPTION', 'A premium digital editorial publication exploring monolithic architecture, tactile interiors, material craftsmanship, and contemporary design perspectives.'),
    'founding_year' => 2026,
    'contact_email' => env('EDITORIAL_CONTACT_EMAIL', 'editorial@atelier-journal.com'),
    'default_og_image' => '/og-default.jpg',
    'navigation' => [
        ['label' => 'Journal', 'href' => '/'],
        ['label' => 'Architecture', 'href' => '/category/architecture-spaces'],
        ['label' => 'Interior', 'href' => '/category/interior-living'],
        ['label' => 'Art & Culture', 'href' => '/category/art-culture'],
        ['label' => 'Objects', 'href' => '/category/product-object-design'],
        ['label' => 'Visual', 'href' => '/category/visual-creative'],
        ['label' => 'About', 'href' => '/about'],
    ],
    'socials' => [
        ['name' => 'Instagram', 'url' => 'https://instagram.com'],
        ['name' => 'Pinterest', 'url' => 'https://pinterest.com'],
        ['name' => 'Substack', 'url' => 'https://substack.com'],
    ],
];
