<?php

namespace PHPFuser;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use \chillerlan\QRCode\QRCode as CQRCode;
use \chillerlan\QRCode\QROptions;
use \Zxing\QrReader;
use \BaconQrCode\Renderer\GDLibRenderer;
use \BaconQrCode\Writer;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;
use PHPFuser\Dumper\QrCodeImageWithLogo;

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
     * Generates a QR code with optional logo embedding and returns it as binary or a Data URI.
     *
     * @param string $data The data to encode in the QR code.
     * @param string|null $logo The path to the logo file to embed in the QR code (optional).
     * @param int $size The size of the QR code image in pixels (default: 400).
     * @param int $margin The margin (quiet zone) around the QR code in pixels (default: 2).
     * @param string $ecl The error correction level: "L", "M", "Q", or "H" (default: "H").
     * @param bool $asDataUri Whether to return the result as a Data URI (default: false).
     * @param string $dataUriMimeType The MIME type to use if returning a Data URI (default: "image/png").
     *
     * @return string The generated QR code as binary data or a Data URI string.
     */
    public static function create(string $data, ?string $logo = null, int $size = 400, int $margin = 2, string $ecl = "H", bool $asDataUri = false, string $dataUriMimeType = "image/png"): string {
        try {
            // Define a mapping of error correction levels to corresponding library objects
            $levels = ["L" => ErrorCorrectionLevel::L(), "M" => ErrorCorrectionLevel::M(), "Q" => ErrorCorrectionLevel::Q(), "H" => ErrorCorrectionLevel::H()];
            // Validate and set the error correction level; default to "H" if invalid
            $ecl = $levels[$ecl] ?? ErrorCorrectionLevel::H();
            // Initialize the renderer with specified size and margin
            $renderer = new GDLibRenderer($size, $margin);
            // Create a QR code writer using the configured renderer
            $writer = new Writer($renderer);
            // Generate the QR code as binary image data
            $result = $writer->writeString($data, Encoder::DEFAULT_BYTE_MODE_ENCODING, $ecl);
            // If a logo is provided, attempt to embed it into the QR code
            if (Utils::isNonNull($logo) && File::isFile($logo)) {
                // Add the logo into the generated QR code; fallback to the original result if embedding fails
                $result = Utils::addLogoIntoImageFromImageBinary($result, $logo) ?: $result;
            }
            // If requested, convert the binary QR code image to a Data URI
            return $asDataUri ? Utils::convertImageBinaryToDataUri($result, $dataUriMimeType) : $result;
        } catch (\Throwable $throwable) {
            return "";
        }
    }

    /**
     * Generates a QR code with optional customization, including logo embedding, color, and scale.
     *
     * @param string $data The data to encode in the QR code.
     * @param string|null $logo The file path to a logo to embed in the QR code (optional).
     * @param int $scale The scaling factor for the QR code (default: 10).
     * @param int $spacing The spacing around the logo (default: 6).
     * @param string $ecl The error correction level: "L", "M", "Q", or "H" (default: "H").
     * @param array $color The RGB color for QR code modules (default: array(22, 39, 130)).
     * @param bool $asDataUri Whether to return the QR code as a Data URI (default: true).
     *
     * @return string The generated QR code as a binary string or Data URI.
     */
    public static function generate(string $data, ?string $logo = null, int $scale = 10, int $spacing = 6, string $ecl = "H", array $color = array(22, 39, 130), bool $asDataUri = true): string {
        try {
            // Initialize the result variable
            $result = "";
            // Define valid error correction levels and their mappings to library constants
            $levels = ["L" => EccLevel::L, "M" => EccLevel::M, "Q" => EccLevel::Q, "H" => EccLevel::H];
            // Set the error correction level, defaulting to "H" if the provided level is invalid
            $ecl = $levels[$ecl] ?? EccLevel::H;
            // Check if the provided logo is a valid and readable file
            $logoIsFile = Utils::isNonNull($logo) && File::isFile($logo) && Utils::isReadable($logo);
            // Default QR code options
            $options = [
                'version' => 5, // QR code version
                'outputType' => QROutputInterface::GDIMAGE_PNG, // Output type (PNG image)
                'returnResource' => false, // Return the image as a string
                'eccLevel' => $ecl, // Error correction level
                'outputBase64' => $asDataUri, // Output as a Base64 Data URI if enabled
                "drawCircularModules" => false, // Use square modules for the QR code
                "scale" => $scale, // Scale of the QR code
                "imageTransparent" => true, // Transparent background
                "drawLightModules" => true, // Draw light (background) modules
                "moduleValues" => [QRMatrix::M_FINDER_DOT => $color] // Customize module colors
            ];
            // If a valid logo is provided, adjust options to reserve space for the logo
            if ($logoIsFile) {
                $options = array_merge($options, [
                    "addLogoSpace"     => true,
                    "logoSpaceWidth"   => $spacing, // Width of the logo space
                    "logoSpaceHeight"  => $spacing, // Height of the logo space
                    "eccLevel"         => EccLevel::H, // Override error correction level for better resilience
                ]);
            }
            // Create QR code options object
            $options = new QROptions($options);
            // Create the QR code instance with the specified options
            $qrcode = new CQRCode($options);
            if ($logoIsFile) {
                // Add data to the QR code and embed the logo
                $qrcode->addByteSegment($data);
                $interface = new QrCodeImageWithLogo($options, $qrcode->getQRMatrix());
                $result = $interface->dump(null, $logo);
            } else {
                // Generate QR code without a logo
                $result = $qrcode->render($data);
            }
            // Return the generated QR code image
            return $result;
        } catch (\Throwable $throwable) {
            return "";
        }
    }

    /**
     * Reads QR code data from an image file.
     * 
     * @param string $filename The path to the QR code image file.
     * 
     * @return bool|string Returns decoded data from QR code or false on failure.
     */
    public static function readFile(string $filename): bool | string {
        try {
            if (File::isFile($filename)) {
                $filename = realpath($filename);
                $reader = new QrReader($filename);
                $result = $reader->text();
                return \is_string($result) ? $result : false;
            }
        } catch (\Throwable $throwable) {
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
        try {
            // Set up options for reading the QR code
            $options = new QROptions;
            $options->readerUseImagickIfAvailable = false; // Prefer GD over Imagick
            $options->readerGrayscale = true; // Read in grayscale for better contrast
            $options->readerIncreaseContrast = true; // Enhance contrast for better readability
            $result = (new CQRCode($options))->readFromFile($filename);
            $result = $result->data;
            return $result;
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * Reads and processes QR code data from a text string.
     * 
     * @param string $text The text content representing QR code data.
     * 
     * @return bool|string Returns decoded data from QR code or false on failure.
     */
    public static function readFromText(string $text): bool|string {
        try {
            // Set up options for reading the QR code
            $options = new QROptions;
            $options->readerUseImagickIfAvailable = false; // Prefer GD over Imagick
            $options->readerGrayscale = true; // Read in grayscale for better contrast
            $options->readerIncreaseContrast = true; // Enhance contrast for better readability
            $result = (new CQRCode($options))->readFromBlob($text);
            $result = $result->data;
            return $result;
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    // PRIVATE METHODS
}
