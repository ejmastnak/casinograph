<?php

/**
 *  Default values
 */

return [
    'default_figure_weight' => 1,
    'restrict_on_delete_message_limit' => 1,
    'figure_sequence' => [
        'default_length' => 10,
        'max_repeated_figures' => 1,
        'infinite_loop_guard' => 10,
    ],
    'graphs' => [
        'casinograph' => [
            'public_file' => 'img/casinograph/public.svg',
            'user_basedir' => 'img/casinograph/users',
            'config' => [
                'graph' => [
                    // 'nodesep' => 0.2,
                    'ranksep' => 0.0,
                ],
            ],
        ],
        'position_graph' => [
            'public_basedir' => 'img/positiongraph/public',
            'user_basedir' => 'img/positiongraph/users',
            'config' => [
                'graph' => [
                    'ranksep' => 0,
                ],
                'root_node' => [
                    'fontname' => "Figtree Medium",
                    'fontsize' => "30pt",
                    'style' => "filled",
                    'fillcolor' => "#bfdbfe",
                    'labelloc' => "c",
                    'target' => "_top",
                ],
            ],
        ],
        'figure_graph' => [
            'public_basedir' => 'img/figuregraph/public',
            'user_basedir' => 'img/figuregraph/users',
            'config' => [
                'graph' => [
                    'rankdir' => 'LR',
                ],
                'node' => [
                    'fontsize' => 12,
                    'fontname' => "Figtree ExtraBold",
                    'fontcolor' => "#172554",
                    'color' => "#172554",
                    'style' => "filled",
                    'fillcolor' => "#eff6ff",
                    'target' => "_top",
                ],
                'edge' => [
                    'fontsize' => 12,
                    'fontname' => "Figtree",
                    'fontcolor' => "#172554",
                    'color' => "#172554",
                    'target' => "_top",
                ],
            ],
        ],
        'compound_figure_graph' => [
            'public_basedir' => 'img/compoundfiguregraph/public',
            'user_basedir' => 'img/compoundfiguregraph/users',
            'config' => [
                'graph' => [
                    'rankdir' => 'LR',
                ],
                'node' => [
                    'fontsize' => 12,
                    'fontname' => "Figtree ExtraBold",
                    'fontcolor' => "#172554",
                    'color' => "#172554",
                    'style' => "filled",
                    'fillcolor' => "#eff6ff",
                    'target' => "_top",
                ],
                'edge' => [
                    'fontsize' => 12,
                    'fontname' => "Figtree",
                    'fontcolor' => "#172554",
                    'color' => "#172554",
                    'target' => "_top",
                ],
            ],
        ],
        'config' => [
            'node' => [
                'fontsize' => "20pt",
                'fontname' => "Figtree ExtraBold",
                'fontcolor' => "#172554",
                'color' => "#172554",
                'style' => "filled",
                'fillcolor' => "#eff6ff",
                'target' => "_top",
            ],
            'edge' => [
                'fontsize' => "20pt",
                'fontname' => "Figtree",
                'fontcolor' => "#172554",
                'color' => "#172554",
                'target' => "_top",
            ],
        ],
    ],
    'position_images' => [
        'public_basedir' => 'img/position_images/public',
        'user_basedir' => 'img/position_images/users',
    ],
];
