<?php

namespace PHPFuser;

/**
 * @author Senestro
 */
class Path {
    // PRIVATE VARIABLE
    // PUBLIC VARIABLES
    // PUBLIC METHODS

    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }

    /**
     * Convert a directory separator to the PHP OS directory separator
     * @param string $path
     * @param string|null $separator Use this to overwrite the default build in DIRECTORY_SEPARATOR constant
     * @return string
     */
    public static function convert_dir_separators(string $path, ?string $separator = null): string {
        // Use the provided separator, or default to the system's DIRECTORY_SEPARATOR if not provided.
        $separator = \is_string($separator) && !empty($separator) ? $separator : DIRECTORY_SEPARATOR;
        return str_ireplace(array("\\", "/"), $separator, $path);
    }

    /**
     * Delete directory separator from the right side after converting it
     * @param string $path
     * @param string|null $separator Use this to overwrite the default build in DIRECTORY_SEPARATOR constant
     * @return string
     * */
    public static function right_delete_dir_separator(string $path, ?string $separator = null): string {
        // Use the provided separator, or default to the system's DIRECTORY_SEPARATOR if not provided.
        $separator = \is_string($separator) && !empty($separator) ? $separator : DIRECTORY_SEPARATOR;
        return rtrim(Path::convert_dir_separators($path, $separator), $separator);
    }

    /**
     * Delete directory separator from the left side after converting it
     * @param string $path
     * @param string|null $separator Use this to overwrite the default build in DIRECTORY_SEPARATOR constant
     * @return string
     * */
    public static function left_delete_dir_separator(string $path, ?string $separator = null): string {
        // Use the provided separator, or default to the system's DIRECTORY_SEPARATOR if not provided.
        $separator = \is_string($separator) && !empty($separator) ? $separator : DIRECTORY_SEPARATOR;
        return ltrim(Path::convert_dir_separators($path, $separator), $separator);
    }

    /**
     * Normalizes the directory separators in the given path, with special handling for Windows paths.
     *
     * This function normalizes the directory separators in a path and can optionally add a separator
     * at the start and end of the path. If the operating system is Windows and the path starts with a 
     * drive letter (e.g., "C:\"), the edges are not closed.
     *
     * @param string $path The directory path to normalize.
     * @param bool $closeEdges If true, adds directory separators at the start and end of the path, unless
     *                         it's a Windows path with a drive letter.
     *                         Default is false.
     * @param string|null $separator The separator to use for the path (defaults to DIRECTORY_SEPARATOR).
     *                               If null or empty, the system's DIRECTORY_SEPARATOR is used.
     * @return string The normalized path with consistent directory separators.
     */
    public static function arrange_dir_separators(string $path, bool $closeEdges = false, ?string $separator = null): string {
        // Use the provided separator, or default to the system's DIRECTORY_SEPARATOR if not provided.
        $separator = \is_string($separator) && !empty($separator) ? $separator : DIRECTORY_SEPARATOR;
        // Convert the path to use consistent separators and split the path into components.
        $explodedPath = array_filter(explode($separator, Path::convert_dir_separators($path, $separator)));
        // Check if the current operating system is Windows.
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        // Check if the path starts with a drive letter (e.g., "C:\").
        $startWithDriveLetter = preg_match('/^[A-Za-z]:[\/\\\]/', $path);
        // Return the path, conditionally closing the edges unless it's a Windows path with a drive letter.
        return ($closeEdges ? (!$isWindows || !$startWithDriveLetter ? $separator : "") : "") . implode($separator, $explodedPath) . ($closeEdges ? $separator : "");
    }


    /**
     * Insert directory separator to the beginning or end of the directory path
     * @param string $path
     * @param bool $toEnd - Defaults to true
     * @param string|null $separator Use this to overwrite the default build in DIRECTORY_SEPARATOR constant
     * @return string
     * */
    public static function insert_dir_separator(string $path, bool $toEnd = true, ?string $separator = null): string {
        // Use the provided separator, or default to the system's DIRECTORY_SEPARATOR if not provided.
        $separator = \is_string($separator) && !empty($separator) ? $separator : DIRECTORY_SEPARATOR;
        // Check if the current operating system is Windows.
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        // Check if the path starts with a drive letter (e.g., "C:\").
        $startWithDriveLetter = preg_match('/^[A-Za-z]:[\/\\\]/', $path);
        return ($toEnd === false ? (!$isWindows || !$startWithDriveLetter ? $separator : "") : "") . Path::convert_dir_separators($path, $separator) . ($toEnd === true ? $separator : "");
    }

    /**
     * Merges two string paths using a specified separator.
     *
     * This method ensures that the first path does not end with the separator and 
     * the second path does not start with the separator before merging them.
     * 
     * If no separator is provided, it defaults to the system's DIRECTORY_SEPARATOR.
     *
     * @param string $a The first path segment.
     * @param string $b The second path segment.
     * @param string|null $separator The separator to use (optional).
     * @return string The merged path.
     */
    public static function merge(string $a, string $b, ?string $separator = null): string {
        // Use the provided separator, or default to the system's DIRECTORY_SEPARATOR if not provided.
        $separator = \is_string($separator) && !empty($separator) ? $separator : DIRECTORY_SEPARATOR;
        $a = Path::right_delete_dir_separator($a, $separator);
        $b = Path::left_delete_dir_separator($b, $separator);
        return $a . $separator . $b;
    }

    /**
     * Merges an array of strings into a single string using a specified separator.
     *
     * This method ensures that all elements in the array are treated as strings
     * before joining them with the specified separator.
     * 
     * If no separator is provided, it defaults to the system's DIRECTORY_SEPARATOR.
     *
     * @param array $a The array of string segments to merge.
     * @param string|null $separator The separator to use (optional).
     * @return string The merged string.
     */
    public static function arrayMerge(array $a, ?string $separator = null): string {
        // Use the provided separator, or default to the system's DIRECTORY_SEPARATOR if not provided.
        $separator = \is_string($separator) && !empty($separator) ? $separator : DIRECTORY_SEPARATOR;
        $b = \array_map("is_string", $a);
        return \implode($separator, $b);
    }

    // PRIVATE METHODS
}
