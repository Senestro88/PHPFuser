<?php

namespace PHPFuser;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;


class Audio {
    // PRIVATE VARIABLE

    // PUBLIC VARIABLES

    // PUBLIC METHODS

    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }


    /**
     * Get the duration of an audio file.
     * The duration is then formatted into a `H:i:s` format (hours:minutes:seconds).
     *
     * @param string $file The path to the audio file whose duration is to be retrieved.
     * @return string The duration of the audio file in `H:i:s` format, or `00:00:00` if invalid.
     */
    public static function getDuration(string $file): string {
        return Utils::getAudioDuration($file);
    }

    /**
     * Set audio metadata tags for a given audio file.
     * 
     * This function sets metadata tags like title, artist, album, year, genre, and more
     * on an audio file (MP3, for example), including an attached cover image.
     *
     * @param string $audioName  The path to the audio file.
     * @param string $coverName  The path to the cover image (usually a PNG or JPEG).
     * @param array  $options    Optional metadata to be set (e.g., title, artist, album, year,genre,  track_number, etc.).
     *
     * @return bool  Returns true on success, false on failure.
     */
    public static function setMetaTags(string $audioName, string $coverName, array $options = array()): bool {
        return Utils::setAudioMetaTags($audioName, $coverName, $options);
    }


    // PRIVATE
}
