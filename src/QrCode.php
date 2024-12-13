<?php

namespace PHPFuser;

use \chillerlan\QRCode\QRCode as ChillerlanQrCode;
use \chillerlan\QRCode\QROptions as ChillerlanQrOptions;
use \Zxing\QrReader;


/**
 * @author Senestro
 */

class QrCode {
    // PRIVATE VARIABLE
    // PUBLIC VARIABLES

    // PUBLIC METHODS

    /**
     * Prevent the constructor from being initialized
     */
    private function __construct() {
    }

    /**
     * Reads QR code data from an image file.
     * 
     * @param string $filename The path to the QR code image file.
     * 
     * @return bool|string Returns decoded data from QR code or false on failure.
     */
    public static function readFile(string $filename): bool | string {
        if (File::isFile($filename)) {
            $filename = realpath($filename);
            $reader = new QrReader($filename);
            $result = $reader->text();
            return \is_string($result) ? $result : false;
        }
        return false;
    }

    /**
     * Reads QR code data from an image file.
     * 
     * @param string $filename The path to the QR code image file.
     * 
     * @return bool|string Returns decoded data from QR code or false on failure.
     */
    public static function readFromFile(string $filename): bool|string {
        // Set up options for reading the QR code
        $options = new ChillerlanQrOptions;
        $options->readerUseImagickIfAvailable = false; // Prefer GD over Imagick
        $options->readerGrayscale = true; // Read in grayscale for better contrast
        $options->readerIncreaseContrast = true; // Enhance contrast for better readability
        $result = (new ChillerlanQrCode($options))->readFromFile($filename);
        $result = $result->data;
        return $result;
    }

    /**
     * Reads and processes QR code data from a text string.
     * 
     * @param string $text The text content representing QR code data.
     * 
     * @return bool|string Returns decoded data from QR code or false on failure.
     */
    public static function readFromText(string $text): bool|string {
        // Set up options for reading the QR code
        $options = new ChillerlanQrOptions;
        $options->readerUseImagickIfAvailable = false; // Prefer GD over Imagick
        $options->readerGrayscale = true; // Read in grayscale for better contrast
        $options->readerIncreaseContrast = true; // Enhance contrast for better readability
        $result = (new ChillerlanQrCode($options))->readFromBlob($text);
        $result = $result->data;
        return $result;
    }
}
