<?php

namespace PHPFuser\Dumper;

use chillerlan\QRCode\Output\QRCodeOutputException;
use chillerlan\QRCode\Output\QRGdImagePNG;
use PHPFuser\File;
use PHPFuser\Utils;

/**
 * @author Senestro
 */
final class QrCodeImageWithLogo extends QRGdImagePNG {
    /**
     * @throws \chillerlan\QRCode\Output\QRCodeOutputException
     */
    public function dump(string|null $file = null, string|null $logo = null): string {
        $logoIsFile = Utils::isNonNull($logo) && File::isFile($logo) && Utils::isReadable($logo);
        // Set returnResource to true to skip further processing for now
        $this->options->returnResource = true;
        if (!$logoIsFile) {
            throw new QRCodeOutputException('Invalid logo.');
        } else {
            // There's no need to save the result of dump() into $this->image here
            parent::dump($file);
            $im = Utils::createImageResourceFromImageFile($logo);
            if ($im instanceof \GdImage) {
                // Get logo image size
                $w = imagesx($im);
                $h = imagesy($im);
                // Set new logo size, leave a border of 1 module (no proportional resize/centering)
                $lw = ($this->options->logoSpaceWidth * $this->options->scale);
                $lh = ($this->options->logoSpaceHeight * $this->options->scale);
                // Get the qrcode size
                $ql = ($this->matrix->getSize() * $this->options->scale);
                // Scale the logo and copy it over. done!
                imagecopyresampled($this->image, $im, (($ql - $lw) / 2), (($ql - $lh) / 2), 0, 0, $lw, $lh, $w, $h);
                $imageData = $this->dumpImage();
                if ($this->options->outputBase64 === true) {
                    $imageData = $this->toBase64DataURI($imageData);
                }
                return $imageData;
            } else {
                throw new QRCodeOutputException('Can not get logo resource.');
            }
        }
    }
}
