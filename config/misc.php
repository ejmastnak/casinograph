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
            'config' => [
                'graph' => [
                    'ratio' => 1.0,
                ],
            ],
        ],
        'position_graph' => [
            'public_basedir' => 'img/positiongraph/public',
            'user_basedir' => 'img/positiongraph/users',
            'config' => [
                'graph' => [
                    'ratio' => 1.0,
                ],
                'root_node' => [
                    'fontname' => "Figtree Medium",
                    'fontsize' => "18pt",
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
            ],
        ],
        'compound_figure_graph' => [
            'public_basedir' => 'img/compoundfiguregraph/public',
            'user_basedir' => 'img/compoundfiguregraph/users',
            'config' => [
                'graph' => [
                    'rankdir' => 'LR',
                ],
            ],
        ],
        'config' => [
            'node' => [
                'fontsize' => "12pt",
                'fontname' => "Figtree ExtraBold",
                'fontcolor' => "#172554",
                'color' => "#172554",
                'style' => "filled",
                'fillcolor' => "#eff6ff",
                'target' => "_top",
            ],
            'edge' => [
                'fontsize' => "12pt",
                'fontname' => "Figtree",
                'fontcolor' => "#172554",
                'color' => "#172554",
                'target' => "_top",
            ],
        ],
    ],
];
