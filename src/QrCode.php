<?php

namespace PHPFuser;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use \chillerlan\QRCode\QRCode as ChillerlanQrCode;
use \chillerlan\QRCode\QROptions as ChillerlanQrOptions;
use \Zxing\QrReader;
use \BaconQrCode\Renderer\GDLibRenderer;
use \BaconQrCode\Writer;

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
     * Creates a QR code with optional logo embedding.
     *
     * @param string $data The data to encode into the QR code.
     * @param string|null $logo Optional binary string of the logo image to embed in the QR code.
     * @param int $size The size (in pixels) of the QR code.
     * @param int $margin The margin (in pixels) around the QR code.
     * @param string $errorCorrectionLevel The error correction level ("L", "M", "Q", "H"). Default is "H".
     *
     * @return string The generated QR code image as a binary string.
     */
    public static function create(string $data, ?string $logo = null, int $size = 400, int $margin = 2, string $errorCorrectionLevel = "H"): string {
        // Define the mapping of error correction levels to their respective objects
        $levels = ["L" => ErrorCorrectionLevel::L(), "M" => ErrorCorrectionLevel::M(), "Q" => ErrorCorrectionLevel::Q(), "H" => ErrorCorrectionLevel::H(),];
        // Validate and assign the error correction level
        $ecl = $levels[$errorCorrectionLevel] ?? ErrorCorrectionLevel::H();
        // Initialize the renderer with the specified size and margin
        $renderer = new GDLibRenderer($size, $margin);
        // Create a QR code writer using the renderer
        $writer = new Writer($renderer);
        // Generate the QR code as a binary string
        $result = $writer->writeString($data, Encoder::DEFAULT_BYTE_MODE_ENCODING, $ecl);
        // Embed the logo into the QR code if provided
        if (Utils::isNonNull($logo)) {
            $result = Utils::logoIntoImageFromImageBinary($result, $logo) ?: $result;
        }
        // Return the final QR code image as a binary string
        return $result;
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

    // PRIVATE METHODS

    /**
     * Get the temporary directory path for storing qrcodes.
     *
     * @return string The path to the temporary directory with proper directory separators.
     */
    private static function getTempDir(): string {
        // Arrange and ensure proper directory separators for the temporary directory path.
        return Path::insert_dir_separator(Path::arrange_dir_separators(PHPFUSER['DIRECTORIES']['DATA'] . DIRECTORY_SEPARATOR . 'qrcodes' . DIRECTORY_SEPARATOR . 'temp'));
    }

    private static function createTempFilename(string $extension): string {
        return self::getTempDir() . Utils::generateRandomFilename($extension);
    }
}
