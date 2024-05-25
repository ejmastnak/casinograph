<?php

/**
 *  Default values
 */

return [
    'default_figure_weight' => 1,
    'restrict_on_delete_message_limit' => 1,
    'graphs' => [
        'casinograph' => [
            'public_file' => 'img/casinograph/public.svg',
            'user_basedir' => 'img/casinograph/users',
        ],
        'position_graph' => [
            'public_basedir' => 'img/positiongraph/public',
            'user_basedir' => 'img/positiongraph/users',
        ],
        'config' => [
            'graph' => [
                'ratio' => 1.0,
            ],
            'node' => [
                'fontname' => "Figtree",
                'fontcolor' => "#172554",
                'color' => "#172554",
                'style' => "filled",
                'fillcolor' => "#eff6ff",
                'target' => "_top",
            ],
            'edge' => [
                'fontname' => "Figtree",
                'fontcolor' => "#172554",
                'color' => "#172554",
                'target' => "_top",
            ],
        ],
    ],
];
