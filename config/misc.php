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
      'position_graph' => [],  // for position graphs
      'config' => [],  // styles for Graphviz
    ],
];
