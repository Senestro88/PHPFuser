<?php

namespace PHPFuser;

use PHPFuser\Utils;
use PHPFuser\Exception\Exception;
use PHPFuser\Exception\InvalidArgumentException;

/**
 * @author Senestro
 */
class Aes {
    // PRIVATE VARIABLE
    // PUBLIC VARIABLES
    // PUBLIC METHODS

    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }
    /**
     * Encrypts data into a raw binary string.
     *
     * @param string $content The content to encrypt.
     * @param string $key The encryption key.
     * @param string $cm The encryption cipher method. Default is "aes-128-cbc".
     * @return null|string Returns a raw binary string on success, otherwise returns null on failure.
     * @throws \PHPFuser\Exception\Exception If an encryption error occurs.
     * @throws \PHPFuser\Exception\InvalidArgumentException If an invalid cipher method is provided.
     */
    public static function enc(string $content, string $key, string $cm = "aes-128-cbc"): ?string {
        // Convert cipher method to lowercase for case-insensitive validation
        $cm = strtolower($cm);
        // Validate that the cipher method is supported by OpenSSL
        if (!in_array($cm, openssl_get_cipher_methods(), true)) {
            throw new InvalidArgumentException("Unknown or invalid cipher method: {$cm}");
        }
        // Determine the required key size based on the cipher method
        $ks = self::determineKeySizeFromCipherMethod($cm);
        if ($ks <= 0) {
            throw new Exception("Unable to determine the key size for the cipher method: {$cm}");
        }
        // Trim unnecessary whitespace from input values
        $content = trim($content);
        $key = trim($key);
        // Validate that the key length matches the required key size
        if ($ks !== strlen($key)) {
            throw new InvalidArgumentException("Invalid key length. Expected {$ks} bytes.");
        }
        try {
            // Retrieve the required IV length for the chosen cipher method
            $ivLength = openssl_cipher_iv_length($cm);
            if (!Utils::isInt($ivLength)) {
                return null;
            }
            // Generate a random Initialization Vector (IV)
            $iv = openssl_random_pseudo_bytes($ivLength);
            // Encrypt the content using OpenSSL
            $result = @openssl_encrypt($content, $cm, $key, OPENSSL_RAW_DATA, $iv);
            // Clear sensitive variables from memory
            $content = $key = "";
            // Concatenate IV and encrypted content for decryption
            return Utils::isString($result) ? Utils::concactStrings($iv, $result) : null;
        } catch (\Exception $e) {
            throw new Exception("Encryption error: " . $e->getMessage());
        }
    }

    /**
     * Decrypts a previously encrypted raw binary string.
     *
     * @param string $content The encrypted content to decrypt.
     * @param string $key The decryption key.
     * @param string $cm The encryption cipher method. Default is "aes-128-cbc".
     * @return null|string Returns the decrypted content on success, otherwise returns null on failure.
     * @throws \PHPFuser\Exception\Exception If a decryption error occurs.
     * @throws \PHPFuser\Exception\InvalidArgumentException If an invalid cipher method is provided.
     */
    public static function dec(string $content, string $key, string $cm = "aes-128-cbc"): ?string {
        // Convert cipher method to lowercase for case-insensitive validation
        $cm = strtolower($cm);
        // Validate that the cipher method is supported by OpenSSL
        if (!in_array($cm, openssl_get_cipher_methods(), true)) {
            throw new InvalidArgumentException("Unknown or invalid cipher method: {$cm}");
        }
        // Determine the required key size based on the cipher method
        $ks = self::determineKeySizeFromCipherMethod($cm);
        if ($ks <= 0) {
            throw new Exception("Unable to determine the key size for the cipher method: {$cm}");
        }
        // Trim unnecessary whitespace from input values
        $content = trim($content);
        $key = trim($key);
        // Validate that the key length matches the required key size
        if ($ks !== strlen($key)) {
            throw new InvalidArgumentException("Invalid key length. Expected {$ks} bytes.");
        }
        try {
            // Retrieve the required IV length for the chosen cipher method
            $ivLength = openssl_cipher_iv_length($cm);
            if (!Utils::isInt($ivLength)) {
                return null;
            }
            // Extract IV and ciphertext from the encrypted data
            $iv = substr($content, 0, $ivLength);
            $cipher = substr($content, $ivLength);
            // Decrypt the ciphertext using OpenSSL
            $result = @openssl_decrypt($cipher, $cm, $key, OPENSSL_RAW_DATA, $iv);
            // Clear sensitive variables from memory
            $content = $key = $cipher = "";
            return Utils::isString($result) ? $result : null;
        } catch (\Exception $e) {
            throw new Exception("Decryption error: " . $e->getMessage());
        }
    }

    /**
     * Determine key size based on cipher method
     * @param string $cm The cipher method
     * @return int Returns 0 when the key size couldn't be determined
     */
    public static function determineKeySizeFromCipherMethod(string $cm): int {
        // Convert the cipher method to lowercase for case-insensitive comparison
        $cm = strtolower($cm);
        // Initialize key size to 0, default value when key size couldn't be determined
        $size = 0;
        // Check if the provided cipher method is valid
        if (in_array($cm, openssl_get_cipher_methods(), true)) {
            // Define an array to map key size patterns to their corresponding sizes
            $kss = array(
                '-128-' => 16, // 128 bits
                '-192-' => 24, // 192 bits
                '-256-' => 32, // 256 bits
            );
            // Iterate over the key size patterns and check if the cipher method matches any of them
            foreach ($kss as $pattern => $ks) {
                if (Utils::containText($pattern, $cm)) {
                    // If a match is found, set the key size and break the loop
                    $size = $ks;
                    break;
                }
            }
        }
        // Return the determined key size, or 0 if it couldn't be determined
        return $size;
    }
}
