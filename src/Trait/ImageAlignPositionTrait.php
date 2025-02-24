<?php

namespace PHPFuser\Trait;

use Spatie\Image\Enums\AlignPosition as SpatieAlignPosition;

/**
 * @author Senestro
 */
trait ImageAlignPositionTrait {
    public function value(): SpatieAlignPosition {
        switch ($this->value) {
            case 'bottom':
                return SpatieAlignPosition::Bottom;
            case 'bottomCenter':
                return SpatieAlignPosition::BottomCenter;
            case 'bottomLeft':
                return SpatieAlignPosition::BottomLeft;
            case 'bottomMiddle':
                return SpatieAlignPosition::BottomMiddle;
            case 'bottomRight':
                return SpatieAlignPosition::BottomRight;
            case 'center':
                return SpatieAlignPosition::Center;
            case 'centerBottom':
                return SpatieAlignPosition::CenterBottom;
            case 'centerCenter':
                return SpatieAlignPosition::CenterCenter;
            case 'centerLeft':
                return SpatieAlignPosition::CenterLeft;
            case 'centerRight':
                return SpatieAlignPosition::CenterRight;
            case 'centerTop':
                return SpatieAlignPosition::CenterTop;
            case 'left':
                return SpatieAlignPosition::Left;
            case 'leftBottom':
                return SpatieAlignPosition::LeftBottom;
            case 'leftCenter':
                return SpatieAlignPosition::LeftCenter;
            case 'leftMiddle':
                return SpatieAlignPosition::LeftMiddle;
            case 'leftTop':
                return SpatieAlignPosition::LeftTop;
            case 'middle':
                return SpatieAlignPosition::Middle;
            case 'middleBottom':
                return SpatieAlignPosition::MiddleBottom;
            case 'middleLeft':
                return SpatieAlignPosition::MiddleLeft;
            case 'middleMiddle':
                return SpatieAlignPosition::MiddleMiddle;
            case 'middleRight':
                return SpatieAlignPosition::MiddleRight;
            case 'middleTop':
                return SpatieAlignPosition::MiddleTop;
            case 'right':
                return SpatieAlignPosition::Right;
            case 'rightBottom':
                return SpatieAlignPosition::RightBottom;
            case 'rightCenter':
                return SpatieAlignPosition::RightCenter;
            case 'rightMiddle':
                return SpatieAlignPosition::RightMiddle;
            case 'rightTop':
                return SpatieAlignPosition::RightTop;
            case 'top':
                return SpatieAlignPosition::Top;
            case 'topCenter':
                return SpatieAlignPosition::TopCenter;
            case 'topLeft':
                return SpatieAlignPosition::TopLeft;
            case 'topMiddle':
                return SpatieAlignPosition::TopMiddle;
            default:
                return SpatieAlignPosition::TopRight;
        }
    }
}
