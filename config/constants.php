<?php

/**
 *  Bespoke config and constant values
 */

return [
    'user_ids' => [
        'casino' => 1,
    ],
    'validation' => [
        'max_name_length' => "max:5000",
        'max_description_length' => "max:10000",
        'max_weight' => "max:1000",
        'max_compound_figure_figures' => "max:1000",
        'max_videos' => "max:1000",
        'max_images' => "max:5",
        'max_image_size' => "10mb",
        'max_url_length' => "max:1000",
        'max_random_walk_length' => "max:500",
    ],
    'seeding' => [
        'db' => storage_path('app/seeding/seed.sqlite'),
        'sqldir' => storage_path('app/seeding/sql'),
        // Array keys should be SQLite table names in seed database
        'sqlscripts' => [
            'position_families' => 'position_families.sql',
            'figure_families' => 'figure_families.sql',
            'positions' => 'positions.sql',
            'figures' => 'figures.sql',
            'compound_figures' => 'compound_figures.sql',
            'compound_figure_figures' => 'compound_figure_figures.sql',
        ],
    ],
];
