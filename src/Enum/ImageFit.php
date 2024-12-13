<?php

namespace PHPFuser\Enum;

use \PHPFuser\Trait\ImageFitTrait;

/**
 * @author Senestro
 */
enum ImageFit: string {
    use ImageFitTrait;
    case Contain = "contain";
    case Crop = "crop";
    case Fill = "fill";
    case FillMax = "fill-max";
    case Max = "max";
    case Stretch = "stretch";
}
