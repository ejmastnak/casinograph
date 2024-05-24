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
