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
            // Grep pattern used to identify line with focused node (to extract
            // its XY coordinates). Logic: first find containing group of
            // node's ellipse element, by searching for e.g. 'id="a_node42"',
            // then count on ellipse element being on line after that.
            'grep' => [
                'after' => 1,
            ],
            'config' => [
                'graph' => [
                    'ranksep' => 0.25,
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
        'position_graph' => [
            'public_basedir' => 'img/positiongraph/public',
            'user_basedir' => 'img/positiongraph/users',
            // Grep pattern used to identify line with root node (to extract
            // its XY coordinates). Logic: since I write the root node first,
            // just find the first ellipse, using `grep -m 1` to only print the
            // first match.
            'grep' => 'ellipse',
            'config' => [
                'graph' => [
                    'ranksep' => 0.3,
                ],
                'root_node' => [
                    'fontname' => "Figtree Medium",
                    'fontsize' => 16,
                    'style' => "filled",
                    'fillcolor' => "#bfdbfe",
                    'labelloc' => "c",
                    'target' => "_top",
                    'id' => 'root',
                ],
                'node' => [
                    'fontsize' => 10,
                    'fontname' => "Figtree ExtraBold",
                    'fontcolor' => "#172554",
                    'color' => "#172554",
                    'style' => "filled",
                    'fillcolor' => "#eff6ff",
                    'target' => "_top",
                ],
                'edge' => [
                    'fontsize' => 10,
                    'fontname' => "Figtree",
                    'fontcolor' => "#172554",
                    'color' => "#172554",
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
    ],
    'position_images' => [
        'public_basedir' => 'img/position_images/public',
        'user_basedir' => 'img/position_images/users',
    ],
];
