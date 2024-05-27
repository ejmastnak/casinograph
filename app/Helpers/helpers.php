<?php

/**
 *  Global helper functions
 */

/**
 *  Returns path to CasinoGraph SVG file for current user relative to app's
 *  public directory.
 */
if (!function_exists('casinoGraphLocalPathForUser')) {
    function casinoGraphLocalPathForUser($userId) {
        return $userId
            ? config('misc.graphs.casinograph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId) . ".svg"
            : config('misc.graphs.casinograph.public_file');
    }
}

/**
 * Returns path to CasinoGraph SVG file for current user relative to server's
 * root directory.
 */
if (!function_exists('casinoGraphFullPathForUser')) {
    function casinoGraphFullPathForUser($userId) {
        return public_path($userId
            ? config('misc.graphs.casinograph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId) . ".svg"
            : config('misc.graphs.casinograph.public_file') 
        );
    }
}

/**
 *  Returns path to Position SVG file for inputted position and current user
 *  relative to app's public directory.
 */
if (!function_exists('positionGraphLocalPathForUser')) {
    function positionGraphLocalPathForUser($positionId, $userId) {
        // Create directory, if needed, to store user's positions
        if (!is_dir(public_path(config('misc.graphs.position_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)))) {
            mkdir(public_path(config('misc.graphs.position_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)));
        }
        return $userId
            ? DIRECTORY_SEPARATOR . config('misc.graphs.position_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId) . DIRECTORY_SEPARATOR . strval($positionId) . ".svg"
            : DIRECTORY_SEPARATOR . config('misc.graphs.position_graph.public_basedir') . DIRECTORY_SEPARATOR . strval($positionId) . ".svg";
    }
}

/**
 *  Returns path to Position SVG file for inputted position and current user
 *  relative to server's root directory.
 */
if (!function_exists('positionGraphFullPathForUser')) {
    function positionGraphFullPathForUser($positionId, $userId) {
        // Create directory, if needed, to store user's positions
        if (!is_dir(public_path(config('misc.graphs.position_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)))) {
            mkdir(public_path(config('misc.graphs.position_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)));
        }
        return public_path($userId
            ? config('misc.graphs.position_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId) . DIRECTORY_SEPARATOR . strval($positionId) . ".svg"
            : config('misc.graphs.position_graph.public_basedir') . DIRECTORY_SEPARATOR . strval($positionId) . ".svg"
        );
    }
}
