<?php

namespace PHPFuser;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;


class Video {
    // PRIVATE VARIABLE

    // PUBLIC VARIABLES

    // PUBLIC METHODS

    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }

    /**
     * Get the duration of a video file.
     * The duration is then formatted into a `H:i:s` format (hours:minutes:seconds).
     *
     * @param string $file The path to the video file whose duration is to be retrieved.
     * @return string The duration of the video file in `H:i:s` format, or `00:00:00` if invalid.
     */
    public static function getDuration(string $file): string {
        return Utils::getVideoDuration($file);
    }

    


    // PRIVATE
}
