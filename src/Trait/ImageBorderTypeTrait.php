<?php

namespace PHPFuser\Trait;

use \Spatie\Image\Enums\BorderType as SpatieBorderType;

/**
 * @author Senestro
 */
trait ImageBorderTypeTrait {
    public function value(): SpatieBorderType {
        switch ($this->value) {
            case 'expand':
                return SpatieBorderType::Expand;
            case 'overlay':
                return SpatieBorderType::Overlay;
            default:
                return SpatieBorderType::Shrink;
        }
    }
}
