<?php

namespace PHPFuser;

use PHPFuser\Utils;
use PHPFuser\Path;

/**
 * @author Senestro
 */
class File {
    // PUBLIC VARIABLES

    // PRIVATE VARIABLES

    /**
     * @var int The default read and write chunk size (2MB)
     */
    private const CHUNK_SIZE = 2097152;

    /**
     * @var int The default read and write chunk size when encrypting or decrypting a file (5MB)
     */
    private const ENC_FILE_INTO_PARTS_CHUNK_SIZE = 5242880;

    // PUBLIC VARIABLES

    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }

    // PUBLIC METHODS

    /**
     * Get file size in bytes
     * @param string $file The filename
     * @return int
     */
    public static function getFilesizeInBytes(string $file): int {
        $bytes = 0;
        if (\is_file($file)) {
            $file = \realpath($file);
            clearstatcache(false, $file);
            $size = @filesize($file);
            if (\is_int($size)) {
                $bytes = $size;
            } else {
                $handle = @fopen($file, 'rb');
                if (\is_resource($handle)) {
                    while (($buffer = fgets($handle, self::CHUNK_SIZE)) !== false) {
                        $bytes += strlen($buffer);
                    }
                }
                fclose($handle);
            }
        }
        return $bytes;
    }

    /**
     * Get file size in kilo bytes
     * @param string $file The filename
     * @return int
     */
    public static function getFilesizeInKB(string $file): int {
        $bytes = self::getFilesizeInBytes($file);
        return $bytes >= 1 ? \round($bytes / 1024) : 0;
    }

    /**
     * Get file size in mega bytes
     * @param string $file The filename
     * @return int
     */
    public static function getFilesizeInMB(string $file): int {
        $kb = self::getFilesizeInKB($file);
        return $kb >= 1 ? \round($kb / 1024) : 0;
    }

    /**
     * Get file size in giga bytes
     * @param string $file The filename
     * @return int
     */
    public static function getFilesizeInGB(string $file): int {
        $mb = self::getFilesizeInMB($file);
        return $mb >= 1 ? \round($mb / 1024) : 0;
    }

    /**
     * Create a file
     * @param string $file
     * @return bool
     */
    public static function createFile(string $file): bool {
        if (Utils::isNotTrue(\is_file($file))) {
            $handle = @fopen($file, "w");
            if (\is_resource($handle)) {
                fclose($handle);
                Utils::setPermissions($file);
            }
        }
        return Utils::isTrue(\is_file($file));
    }

    /**
     * Saves the provided content to a file. Optionally, appends the content
     * and adds a newline before the content if specified.
     * 
     * @param string $file    The path to the file where the content will be saved.
     * @param string $content The content to be written to the file.
     * @param bool   $append  Whether to append the content to the file. Defaults to false.
     * @param bool   $newline Whether to add a newline before the content if appending. Defaults to false.
     * 
     * @return bool Returns true if the content was successfully written, false otherwise.
     */
    public static function saveContentToFile(string $file, string $content, bool $append = false, bool $newline = false): bool {
        $saved = false;
        // Check if the file creation is successful.
        if (self::createFile($file)) {
            // Open the file for writing, either in append or write mode.
            $handle = @fopen($file, $append ? 'a' : 'w');
            // Check if the file was successfully opened.
            if (\is_resource($handle)) {
                // Lock the file for writing (no need for flock here, self::lockFile handles it).
                if (self::lockFile($handle, false)) {
                    // If appending and newline is enabled, add a newline before the content.
                    if ($append && $newline && self::getFilesizeInBytes($file) >= 1) {
                        $content = "\n" . $content;
                    }
                    // Write the content to the file handle.
                    $saved = self::writeContentToHandle($handle, $content);
                    // Flush the output to ensure data is written before releasing the lock.
                    fflush($handle);
                    // Release the lock after writing.
                    self::unlockFile($handle);
                }
                // Close the file handle after the operation is complete.
                fclose($handle);
            }
        }
        // Return whether the content was successfully saved.
        return $saved;
    }



    /**
     * Reads the content of a file and returns it as a string. The file is read in binary mode,
     * and the content is read in chunks to avoid memory issues with large files.
     * 
     * @param string $file The path to the file to be read.
     * 
     * @return string The content of the file, or an empty string if the file cannot be read.
     */
    public static function getFileContent(string $file): string {
        $content = '';
        // Check if the file exists.
        if (\is_file($file)) {
            // Open the file in binary read mode.
            $handle = @fopen($file, 'rb');
            // Check if the file was opened successfully and if it can be locked.
            if (\is_resource($handle) && self::lockFile($handle)) {
                // Read the file content in chunks to avoid memory overload.
                while (!feof($handle)) {
                    $read = fread($handle, self::CHUNK_SIZE); // Read a chunk of the file.
                    // Check if the read operation returned a string (valid content).
                    if (\is_string($read)) {
                        $content .= $read; // Append the chunk to the content.
                    } else {
                        break; // Stop reading if an invalid chunk is returned.
                    }
                }
                // Release the file lock after reading.
                self::unlockFile($handle);
                fclose($handle); // Close the file handle.
            }
        }

        // Return the file content, which is an empty string if the file couldn't be read.
        return $content;
    }

    /**
     * Writes content to a file handle in chunks.
     * 
     * @param mixed $handle The file handle.
     * @param string $content The content to be written to the file.
     * 
     * @return bool Returns true if content was successfully written, false if there was an error.
     */
    public static function writeContentToHandle(mixed $handle, string $content): bool {
        $offset = 0;
        // Check if the handle is a valid resource.
        if (\is_resource($handle)) {
            // Loop through the content in chunks.
            while ($offset < strlen($content)) {
                $chunk = substr($content, $offset, self::CHUNK_SIZE);
                // Attempt to write the chunk to the file handle.
                if (@fwrite($handle, $chunk) === false) {
                    // If writing fails, exit the loop and return false.
                    return false;
                }
                // Move the offset by the chunk size.
                $offset += self::CHUNK_SIZE;
            }
        }
        // Return true if at least one byte was written, false otherwise.
        return $offset > 0;
    }


    /**
     * Gets file type
     * @param string $file The file path
     * @return string | bool
     */
    public static function getType(string $file): string|bool {
        if (\is_file($file)) {
            $realPath = \realpath($file);
            if (\is_string($realPath)) {
                return @filetype(\realpath($realPath));
            }
        }
        return "unknown";
    }


    /**
     * Gives information about a file or symbolic link
     * @param string $file The file path
     * @return array
     */
    public static function getStats(string $file): array {
        return \is_file($file) && \is_array($stat = @lstat($file)) ? $stat : array();
    }

    /**
     * Gets file info
     * @param string $file The file path
     * @return array
     */
    public static function getInfo(string $file): array {
        if (\is_file($file)) {
            $array = array();
            $i = new \SplFileInfo($file);
            $array['realpath'] = $i->getRealPath();
            $array['path'] = self::getDirname($i->getRealPath());
            $array['basename'] = $i->getBasename();
            $array['extension'] = $i->getExtension();
            $array['filename'] = $i->getFilename();
            $size = $i->getSize();
            $sizes = array_change_key_case(\PHPFuser\Utils::convertBytes($size), CASE_LOWER);
            $array['size'] = array('numbers' => \array_merge(array('b' => $size), $sizes), 'string' => Utils::formatSize($size));
            $array['access_time'] = array('number' => $i->getATime(), 'string' => Utils::readableUnix($i->getATime()));
            $array['modified_time'] = array('number' => $i->getMTime(), 'string' => Utils::readableUnix($i->getMTime()));
            $array['inode_change_time'] = array('number' => $i->getCTime(), 'string' => Utils::readableUnix($i->getCTime()));
            $array['mime_type'] = Utils::getMime($file);
            $array['type'] = $i->getType();
            $array['is_directory'] = $i->isDir();
            $array['is_file'] = $i->isFile();
            $array['permission'] = array('number' => Utils::getPermission($file), 'string' => Utils::getReadablePermission($file));
            $array['owner'] = array('number' => $i->getOwner(), 'string' => (function_exists("posix_getpwuid") ? posix_getpwuid($i->getOwner()) : ""));
            $array['group'] = array('number' => $i->getGroup(), 'string' => (function_exists("posix_getgrgid") ? posix_getgrgid($i->getGroup()) : ""));
            $array['is_link'] = $i->isLink();
            $array['link_target'] = $i->isLink() ? $i->getLinkTarget() : "";
            $array['is_executable'] = $i->isExecutable();
            $array['is_readable'] = $i->isReadable();
            $array['is_writable'] = $i->isWritable();
            return $array;
        }
        return array();
    }

    /**
     * Touch a file (Sets access and modification time of file)
     * @param string $file The file path
     * @param int $mtime Modified time, defaults to 'null'
     * @param int $atime Access time, defaults to 'null'
     * @return bool
     */
    public static function touchFile(string $file, ?int $mtime = null, ?int $atime = null): bool {
        return @touch($file, $mtime, $atime);
    }

    /**
     * Get file extension
     * @param string $file The file path
     * @return string
     */
    public static function getExtension(string $file): string {
        $info = pathinfo($file);
        return \is_array($info) && isset($info['extension']) ? $info['extension'] : "";
    }

    /**
     * Get directory name of a file or directory
     *
     * @param string $file
     * @return string
     */
    public static function getDirname(string $file): string {
        $info = pathinfo($file);
        return \is_array($info) && isset($info['dirname']) ? $info['dirname'] : "";
    }

    /**
     * Remove extension from a filename
     * @param string $file he file path
     * @return string
     */
    public static function removeExtension(string $file): string {
        $extension = self::getExtension($file);
        if (Utils::isNotEmptyString($extension)) {
            return substr($file, 0, - (strlen($extension) + 1));
        }
        return $file;
    }

    /**
     * Delete a file
     * @param string $file
     * @return bool
     */
    public static function deleteFile(string $file): bool {
        if (self::isFile($file)) {
            return @unlink($file);
        }
        return false;
    }

    /**
     * Tells whether the filename is a regular file
     * @param string $file
     * @return bool
     */
    public static function isFile(string $file): bool {
        return @is_file($file);
    }

    /**
     * Tells whether the filename is not a regular file
     * @param string $file
     * @return bool
     */
    public static function isNotFile(string $file): bool {
        return !self::isFile($file);
    }

    /**
     * Tells whether the filename is a symbolic link
     * @param string $file
     * @return bool
     */
    public static function isLink(string $file): bool {
        return @is_link($file);
    }

    /**
     * Tells whether the filename is not a symbolic link
     * @param string $file
     * @return bool
     */
    public static function isNotLink(string $file): bool {
        return !self::isLink($file);
    }

    /**
     * Rename a file or directory in the current directory (Eg. C:/User/Public/Text.txt to C:/User/Public/Text.php)
     * @param string $source The source file
     * @param string $name The new name
     * @return bool
     */
    public static function rename(string $source, string $name): bool {
        if (Utils::isExists($source)) {
            $dirname = Path::arrange_dir_separators(self::getDirname($source));
            $name = \basename($name);
            return @rename($source, $dirname . DIRECTORY_SEPARATOR . $name);
        }
        return false;
    }

    /**
     * Copy a file to destination
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public static function copyFile(string $source, string $destination): bool {
        if (self::isFile($destination)) {
            return true;
        } elseif (self::isFile($source) && \strtolower($source) !== \strtolower($destination) && @copy($source, $destination)) {
            Utils::setPermissions($destination);
            return true;
        }
        return false;
    }

    /**
     * Copy a file to the destination directory
     * @param string $source The source file
     * @param string $dir The destination directory
     * @param string|null $name The new name but can be null
     * @return bool
     */
    public static function copyFileToDir(string $source, string $dir, ?string $name = null): bool {
        if (self::isFile($source)) {
            self::createDir($dir);
            $dir = Path::arrange_dir_separators(\realpath($dir));
            $destination = $dir . DIRECTORY_SEPARATOR . (\is_string($name) && !empty($name) ? $name : basename($source));
            if (self::isFile($destination)) {
                return true;
            } elseif (@copy($source, $destination)) {
                Utils::setPermissions($destination);
                return true;
            }
        }
        return false;
    }

    /**
     * Move a file or directory to destination
     * @param string $source The source file
     * @param string $dir The destination directory
     * @param string|null $name The new name but can be null
     * @return bool
     */
    public static function moveFileOrDirToDir(string $source, string $dir, ?string $name = null): bool {
        if (Utils::isExists($source)) {
            self::createDir($dir);
            $dir = Path::arrange_dir_separators(\realpath($dir));
            $destination = $dir . DIRECTORY_SEPARATOR . (\is_string($name) && !empty($name) ? $name : basename($source));
            if (Utils::isExists($destination)) {
                return true;
            } elseif (@rename($source, $destination)) {
                Utils::setPermissions($destination);
                return true;
            }
        }
        return false;
    }

    /**
     * Copy source directory to destination directory
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public static function copyDir(string $source, string $destination): bool {
        if (self::isDir($source) && self::createDir($destination)) {
            $destination = Path::right_delete_dir_separator($destination);
            // Make directory comes first
            $lists = array_reverse(Utils::sortFilesFirst(self::scanDirRecursively($source)));
            // The base destination directory
            $base = $destination . DIRECTORY_SEPARATOR . basename($source);
            if (self::createDir($base)) {
                foreach ($lists as $srcPath) {
                    $destPath = Path::arrange_dir_separators($base . DIRECTORY_SEPARATOR . str_replace($source, "", $srcPath));
                    if (self::isDir($srcPath)) {
                        // Create the directory in the destination
                        if (!self::isDir($destPath) && !self::createDir($destPath)) {
                            return false; // Failed to create directory
                        }
                    } else {
                        // Copy the file
                        if (!self::copyFile($srcPath, $destPath)) {
                            return false; // Failed to copy file
                        }
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Open a directory recursively and list out the files
     * @param string $dir The directory path
     * @return array
     */
    public static function scanDirRecursively(string $dir): array {
        $lists = array();
        if (!empty($dir) && \is_dir($dir) && \is_readable($dir)) {
            $dir = \realpath($dir);
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::CURRENT_AS_FILEINFO), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $info) {
                $lists[] = \realpath($info->getRealPath());
            }
            $i = null;
            unset($i);
        }
        return $lists;
    }

    /**
     * Scan a directory and return files list
     * @param string $dir The directory path
     * @return array
     */
    public static function scanDir(string $dir): array {
        $lists = array();
        if (\is_dir($dir) && \is_readable($dir)) {
            $dir = \realpath($dir);
            $iterator = new \IteratorIterator(new \DirectoryIterator($dir));
            foreach ($iterator as $info) {
                if (!$info->isDot()) {
                    $lists[] = \realpath($info->getRealPath());
                }
            }
        }
        return $lists;
    }

    /**
     * Scan a directory recursively for patterns
     * @param string $dir The directory path
     * @param string $pattern The pattern
     * @param bool $recursive
     * @return array
     */
    public static function scanDirForPattern(string $dir, string $pattern = "", bool $recursive = false): array {
        $lists = array();
        if (!empty($dir) && \is_dir($dir) && \is_readable($dir)) {
            $dir = \realpath($dir);
            $iterator = $recursive ? new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::CURRENT_AS_SELF) : new \DirectoryIterator($dir);
            if (!empty($pattern)) {
                $CallbackFilterIterator = $recursive ? "\RecursiveCallbackFilterIterator" : "\CallbackFilterIterator";
                $iterator = new $CallbackFilterIterator($iterator, function ($current) use ($pattern) {
                    // TRUE to accept the current item to the iterator, FALSE otherwise
                    if ($current->isDir() && !$current->isDot()) {
                        return true;
                    } else {
                        return Utils::matchFilename($current->getRealPath(), $pattern);
                    }
                });
            }
            $iterator = $recursive ? new \RecursiveIteratorIterator($iterator) : new \IteratorIterator($iterator);
            foreach ($iterator as $key => $info) {
                $lists[] = \realpath($info->getRealPath());
            }
        }
        return $lists;
    }

    /**
     * Gets the size of a directory
     * @param string $dir The directory path
     * @param bool $recursive
     * @return int
     */
    public static function getDirSize(string $dir, bool $recursive = true): int {
        $size = 0;
        $files = Utils::isTrue($recursive) ? self::scanDirRecursively($dir) : self::scanDir($dir);
        foreach ($files as $index => $value) {
            if (\is_file($value) && \is_readable($value)) {
                $size += self::getFilesizeInBytes($value);
                clearstatcache(false, $value);
            }
        }
        $files = null;
        unset($files);
        return $size;
    }

    /**
     * Gets the information of files in a directory
     * @param string $dir The directory path
     * @param bool $recursive
     * @return array
     */
    public static function getDirFilesInfo(string $dir, bool $recursive = true): array {
        $array = array();
        $files = Utils::isTrue($recursive) ? self::scanDirRecursively($dir) : self::scanDir($dir);
        foreach ($files as $file) {
            if (\is_file($file)) {
                $array[] = self::getInfo($file);
            } elseif (\is_dir($file)) {
                continue; // Skip directory
            }
        }
        $files = null;
        unset($files);
        return $array;
    }

    /**
     * Searches for files in a directory that match specified names.
     * @param string $dir The directory path to search within.
     * @param array $names An array of names to search for. Can be filenames or extensions based on $asExtension.
     * @param bool $asExtension If true, searches for files with the specified extensions; otherwise, searches for filenames containing the names.
     * @param bool $recursive If true, searches directories recursively; otherwise, searches only the top-level directory.
     * @return array An array of matching file paths.
     */
    public static function searchDir(string $dir, array $names = array(), bool $asExtension = false, bool $recursive = true): array {
        $results = array();
        // Get files list, either recursively or non-recursively and normalize and lowercase search patterns
        $files = array_map('strtolower', $recursive ? self::scanDirRecursively($dir) : self::scanDir($dir));
        // Iterate through each file in the directory
        foreach ($files as $file) {
            $basename = basename($file);
            // Check each match pattern
            foreach ($names as $name) {
                $name = strtolower($name);
                // If searching by extension, check the file extension
                if ($asExtension) {
                    $extension = \strtolower(self::getExtension($file));
                    if ($name == $extension) {
                        $results[] = $file;
                        break; // No need to check other names if a match is found
                    }
                } else {
                    if (Utils::containText($name, $basename)) {
                        // Otherwise, check if the file name contains the match string
                        $results[] = $file;
                        break; // No need to check other names if a match is found
                    }
                }
            }
        }
        return $results;
    }

    /**
     * Delete a file based on extensions
     * @param string $dir
     * @param array $extensions
     * @param bool $recursive: Default to 'true'
     * @return void
     */
    public static function deleteFilesBaseOnExtensionsInDir(string $dir, array $extensions = array(), bool $recursive = true): void {
        if (\is_dir($dir) && \is_readable($dir)) {
            // Ensure the directory path ends with a separator
            $dir = Path::insert_dir_separator(\realpath($dir));
            // Get files list, either recursively or non-recursively and normalize and lowercase search patterns
            $files = array_map('strtolower', $recursive ? self::scanDirRecursively($dir) : self::scanDir($dir));
            // Iterate through each file in the directory
            foreach ($files as $file) {
                foreach ($extensions as $extension) {
                    $ext = \strtolower(self::getExtension($file));
                    if ($extension === $ext) {
                        self::deleteFile($file);
                        break; // No need to check other extension as the file is deleted
                    }
                }
            }
        }
    }

    /**
     * Make a directory. This function will return true if the directory already exist
     * @param string $dir
     * @return bool
     */
    public static function createDir(string $dir): bool {
        if (\is_dir($dir)) {
            Utils::setPermissions($dir);
            return true;
        } else {
            return @mkdir($dir, Utils::DIRECTORY_PERMISSION, true);
        }
        return false;
    }

    /**
     * Delete a directory
     * @param string $dir
     * @return bool
     */
    public static function deleteDir(string $dir): bool {
        return File::emptyDirectory($dir, true);
    }

    /**
     * Empty a directory
     * @param string $dir
     * @param bool $delete Wether to delete the directory is self after deleting the directory contents
     * @return bool
     */
    public static function emptyDirectory(string $dir, bool $delete = false): bool {
        if (\is_dir($dir)) {
            $i = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($i as $list) {
                $list = \realpath($list->getRealPath());
                if (\is_file($list)) {
                    self::deleteFile($list);
                } elseif (\is_dir($list)) {
                    @rmdir($list);
                }
            }
            if (Utils::isTrue($delete)) {
                return @rmdir($dir);
            }
            return true;
        }
        return false;
    }

    /**
     * Tells whether the filename is a directory
     * @param string $dirname
     * @return bool
     */
    public static function isDir(string $dirname): bool {
        return @is_dir($dirname);
    }

    /**
     * Tells whether the filename is not a directory
     * @param string $dirname
     * @return bool
     */
    public static function isNotDir(string $dirname): bool {
        return !self::isDir($dirname);
    }

    /**
     * Check if directory is empty
     * @param string $dirname
     * @return bool
     */
    public static function isEmptyDir(string $dirname): bool {
        return (self::isDir($dirname)) ? !(new \FilesystemIterator($dirname))->valid() : false;
    }

    /**
     * Check if directory is not empty
     * @param string $dirname
     * @return bool
     */
    public static function isNotEmptyDir(string $dirname): bool {
        return !self::isEmptyDir($dirname);
    }

    /**
     * Encrypt (AES) file into parts and save in directory
     * @param string $sourceFile The file to encrypt
     * @param string $toPath The directory to save the parts
     * @param string $key The encryption key
     * @param string $iv The encryption iv
     * @param string $cm The encryption cipher method
     * @return bool
     */
    public static function encFileIntoParts(string $sourceFile, string $toPath, string $key, string $iv, string $cm = "aes-128-cbc"): bool {
        if (in_array($cm, openssl_get_cipher_methods())) {
            try {
                if (self::isFile($sourceFile)) {
                    if (self::isNotFile($toPath)) {
                        self::createFile($toPath);
                    }
                    $chunkSize = self::ENC_FILE_INTO_PARTS_CHUNK_SIZE;
                    $index = 1;
                    $startBytes = 0;
                    $totalBytes = self::getFilesizeInBytes($sourceFile);
                    while ($startBytes < $totalBytes) {
                        $remainingBytes = $totalBytes - $startBytes;
                        $chunkBytes = min($chunkSize, $remainingBytes);
                        $plainText = @file_get_contents($sourceFile, false, null, $startBytes, $chunkBytes);
                        if ($plainText !== false) {
                            $file = Path::insert_dir_separator($toPath) . '' . $index . '.part';
                            $index += 1;
                            $startBytes += $chunkBytes;
                            $encryptedText = @openssl_encrypt($plainText, $cm, $key, $option = OPENSSL_RAW_DATA, $iv);
                            if ($encryptedText !== false) {
                                self::saveContentToFile($file, $encryptedText);
                            }
                        }
                    }
                    return true;
                }
            } catch (\Throwable $e) {
            }
        }
        return false;
    }

    /**
     * Decrypt (AES) a directory parts into a single file @see encFileIntoParts
     * @param string $sourcePath The parts directory
     * @param string $toFilename The filename to append parts to it
     * @param string $key The decryption key
     * @param string $iv The decryption iv
     * @param string $cm The decryption cipher method
     * @return bool
     */
    public static function decPartsIntoFile(string $sourcePath, string $toFilename, string $key, string $iv, string $cm = "aes-128-cbc"): bool {
        if (in_array($cm, openssl_get_cipher_methods())) {
            if (self::isFile($sourcePath)) {
                if (self::isFile($toFilename)) {
                    self::deleteFile($toFilename);
                }
                $dirFiles = @scandir($sourcePath, $sortingOrder = SCANDIR_SORT_NONE);
                $numOfParts = 0;
                if ($dirFiles != false) {
                    foreach ($dirFiles as $currentFile) {
                        if (preg_match('/^\d+\.part$/', $currentFile)) {
                            $numOfParts++;
                        }
                    }
                }
                if ($numOfParts >= 1) {
                    for ($index = 1; $index <= $numOfParts; $index++) {
                        $file = Path::insert_dir_separator($sourcePath) . '' . $index . '.part';
                        if (self::isFile($file)) {
                            $cipherText = @file_get_contents($file, false, null, 0, null);
                            if (self::isNotFalse($cipherText)) {
                                self::deleteFile($file);
                                $decryptedText = @openssl_decrypt($cipherText, $cm, $key, $option = OPENSSL_RAW_DATA, $iv);
                                if ($decryptedText !== false) {
                                    self::saveContentToFile($toFilename, $decryptedText, true);
                                }
                            }
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }

    public static function isNotFalse(mixed $arg): bool {
        return !($arg === false);
    }

    public static function isFalse(mixed $arg): bool {
        return $arg === false;
    }

    /**
     * Locks a file for reading or writing to prevent concurrent access.
     * 
     * @param mixed $handle The stream handle to the file that needs to be locked.
     * @param bool  $reading Whether the lock should be for reading (`LOCK_SH`) or writing (`LOCK_EX`).
     * @param bool  $wait Whether to block the operation and wait for the lock to be acquired (`true`), or return immediately if the lock cannot be acquired (`false`).
     * 
     * @return bool Returns `true` if the file was successfully locked, `false` otherwise.
     */
    public static function lockFile(mixed $handle, bool $reading = true, bool $wait = false): bool {
        // Set the type of lock based on the reading or writing mode.
        $operation = $reading ? LOCK_SH : LOCK_EX;
        // Attempt to lock the file, blocking if $wait is true, otherwise non-blocking.
        return \is_resource($handle) && \flock($handle, $wait ? $operation : $operation | LOCK_NB);
    }

    /**
     * Unlocks a previously locked file.
     * 
     * @param mixed $handle The stream handle to the file that needs to be unlocked.
     * 
     * @return bool Returns `true` if the file was successfully unlocked, `false` otherwise.
     */
    public static function unlockFile(mixed $handle): bool {
        // Attempt to unlock the file.
        return \is_resource($handle) && \flock($handle, LOCK_UN);
    }

}
