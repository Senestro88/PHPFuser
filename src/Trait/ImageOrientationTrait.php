<?php

namespace PHPFuser\Trait;

use \Spatie\Image\Enums\Orientation as SpatieOrientation;

/**
 * @author Senestro
 */
trait ImageOrientationTrait {
    public function value(): SpatieOrientation {
        switch ($this->value) {
            case 'rotate0':
                return SpatieOrientation::Rotate0;
            case 'rotate90':
                return SpatieOrientation::Rotate90;
            case 'rotate180':
                return SpatieOrientation::Rotate180;
            default:
                return SpatieOrientation::Rotate270;
        }
    }
}
