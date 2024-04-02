<?php

/**
 *  Global helper functions
 */

/**
 * Path to casinograph SVG for current user relative to app's public directory
 */
if (!function_exists('casinographPathForUser')) {
    function casinographPathForUser($userId) {
        $parent_dir = config('misc.casinograph.parent_dir');
        $filename = "graph" . ($userId ? ("-" . strval($userId)) : "") . ".svg";
        return $parent_dir . DIRECTORY_SEPARATOR . $filename;
    }
}
