<?php

namespace PHPFuser\Trait;

use \Spatie\Image\Enums\Fit as SpatieFit;

/**
 * @author Senestro
 */
trait ImageFitTrait {
    public function value(): SpatieFit {
        switch ($this->value) {
            case 'contain':
                return SpatieFit::Contain;
            case 'crop':
                return SpatieFit::Crop;
            case 'fill':
                return SpatieFit::Fill;
            case 'fill-max':
                return SpatieFit::FillMax;
            case 'max':
                return SpatieFit::Max;
            default:
                return SpatieFit::Stretch;
        }
    }
}
