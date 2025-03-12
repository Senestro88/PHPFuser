<?php

namespace PHPFuser;

/**
 * @author Senestro
 */
class Mail {
    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }


    public static function log(string $message): void {
        $filename = Path::merge(getenv("DOCUMENT_ROOT"), "\$maillog.log");
        File::saveContentToFile($filename, $message, true, true);
    }
}
