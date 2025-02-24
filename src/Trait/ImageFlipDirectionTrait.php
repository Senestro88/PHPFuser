<?php

namespace PHPFuser\Trait;

use \Spatie\Image\Enums\FlipDirection as SpatieFlipDirection;

/**
 * @author Senestro
 */
trait ImageFlipDirectionTrait {
    public function value(): SpatieFlipDirection {
        switch ($this->value) {
            case 'vertical':
                return SpatieFlipDirection::Vertical;
            case 'horizontal':
                return SpatieFlipDirection::Horizontal;
            default:
                return SpatieFlipDirection::Both;
        }
    }
}
