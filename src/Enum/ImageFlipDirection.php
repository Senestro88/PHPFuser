<?php

namespace PHPFuser\Enum;

use \PHPFuser\Trait\ImageFlipDirectionTrait;

/**
 * @author Senestro
 */
enum ImageFlipDirection: string {
    use ImageFlipDirectionTrait;
    case Vertical = "vertical";
    case Horizontal = "horizontal";
    case Both = "both";
}
