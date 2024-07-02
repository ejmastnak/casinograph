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
        return
            $userId && $userId !== config('constants.user_ids.casino')
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
        return public_path(
            $userId && $userId !== config('constants.user_ids.casino')
            ? config('misc.graphs.casinograph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId) . ".svg"
            : config('misc.graphs.casinograph.public_file') 
        );
    }
}

/**
 *  Returns path to Position SVG file for inputted position and current user
 *  relative to app's public directory.
 */
if (!function_exists('positionGraphPublicPathForUser')) {
    function positionGraphPublicPathForUser($positionId, $userId) {
        $svgDir = $userId && $userId !== config('constants.user_ids.casino')
        ? config('misc.graphs.position_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)
        : config('misc.graphs.position_graph.public_basedir');

        return
            $userId && $userId !== config('constants.user_ids.casino')
            ? DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($positionId) . ".svg"
            : DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($positionId) . ".svg";
    }
}

/**
 *  Returns path to Position SVG file for inputted position and current user
 *  relative to server's root directory, used for storing SVG files on disk.
 */
if (!function_exists('positionGraphStoragePathForUser')) {
    function positionGraphStoragePathForUser($positionId, $userId) {
        $svgDir = $userId && $userId !== config('constants.user_ids.casino')
        ? config('misc.graphs.position_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)
        : config('misc.graphs.position_graph.public_basedir');

        // Create directory, if needed, to store user's position SVG files
        if (!is_dir(public_path($svgDir))) {
            mkdir(public_path($svgDir));
        }

        return public_path(
            $userId && $userId !== config('constants.user_ids.casino')
            ? DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($positionId) . ".svg"
            : DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($positionId) . ".svg"
        );
    }
}

/**
 *  Returns path to Figure SVG file for inputted figure and current user
 *  relative to app's public directory.
 */
if (!function_exists('figureGraphPublicPathForUser')) {
    function figureGraphPublicPathForUser($figureId, $userId) {
        $svgDir = $userId && $userId !== config('constants.user_ids.casino')
        ? config('misc.graphs.figure_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)
        : config('misc.graphs.figure_graph.public_basedir');

        return
            $userId && $userId !== config('constants.user_ids.casino')
            ? DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($figureId) . ".svg"
            : DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($figureId) . ".svg";
    }
}

/**
 *  Returns path to Figure SVG file for inputted figure and current user
 *  relative to server's root directory, used for storing SVG files on disk.
 */
if (!function_exists('figureGraphStoragePathForUser')) {
    function figureGraphStoragePathForUser($figureId, $userId) {
        $svgDir = $userId && $userId !== config('constants.user_ids.casino')
        ? config('misc.graphs.figure_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)
        : config('misc.graphs.figure_graph.public_basedir');

        // Create directory, if needed, to store user's position SVG files
        if (!is_dir(public_path($svgDir))) {
            mkdir(public_path($svgDir));
        }

        return public_path(
            $userId && $userId !== config('constants.user_ids.casino')
            ? DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($figureId) . ".svg"
            : DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($figureId) . ".svg"
        );
    }
}

/**
 *  Returns path to Figure SVG file for inputted compound figure and current
 *  user relative to app's public directory.
 */
if (!function_exists('compoundFigureGraphPublicPathForUser')) {
    function compoundFigureGraphPublicPathForUser($compoundFigureId, $userId) {
        $svgDir = $userId && $userId !== config('constants.user_ids.casino')
        ? config('misc.graphs.compound_figure_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)
        : config('misc.graphs.compound_figure_graph.public_basedir');

        return
            $userId && $userId !== config('constants.user_ids.casino')
            ? DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($compoundFigureId) . ".svg"
            : DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($compoundFigureId) . ".svg";
    }
}

/**
 *  Returns path to Figure SVG file for inputted compound figure and current
 *  user relative to server's root directory.
 */
if (!function_exists('compoundFigureGraphStoragePathForUser')) {
    function compoundFigureGraphStoragePathForUser($compoundFigureId, $userId) {
        $svgDir = $userId && $userId !== config('constants.user_ids.casino')
        ? config('misc.graphs.compound_figure_graph.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)
        : config('misc.graphs.compound_figure_graph.public_basedir');

        // Create directory, if needed, to store user's position SVG files
        if (!is_dir(public_path($svgDir))) {
            mkdir(public_path($svgDir));
        }

        return public_path(
            $userId && $userId !== config('constants.user_ids.casino')
            ? DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($compoundFigureId) . ".svg"
            : DIRECTORY_SEPARATOR . $svgDir . DIRECTORY_SEPARATOR . strval($compoundFigureId) . ".svg"
        );
    }
}

/**
 *  Returns path to user's position_image directory relative to root of `local`
 *  disk (used for storage of position images).
 */
if (!function_exists('positionImageStoragePathForUser')) {
    function positionImageStoragePathForUser($userId) {
        $dir = $userId && $userId !== config('constants.user_ids.casino')
        ? config('misc.position_images.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)
        : config('misc.position_images.public_basedir');

        // Create directory, if needed, to store user's position image files
        if (!is_dir(storage_path('app' . DIRECTORY_SEPARATOR . $dir))) {
            mkdir(storage_path('app' . DIRECTORY_SEPARATOR . $dir));
        }

        return $dir;
    }
}

/**
 * Returns path to user's position_image directory relative to `public` folder
 * (used to publically display position images).
 */
if (!function_exists('positionImagePublicPathForUser')) {
    function positionImagePublicPathForUser($userId) {
        return $userId && $userId !== config('constants.user_ids.casino')
            ? config('misc.position_images.user_basedir') . DIRECTORY_SEPARATOR . strval($userId)
            : config('misc.position_images.public_basedir');
    }
}
