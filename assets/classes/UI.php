<?php

declare(strict_types=1);

namespace Mistralys\SeriesManager;

class UI
{
    public static function prettyBool(bool $value) : string
    {
        if($value) {
            return '<i class="text-success glyphicon glyphicon-ok-circle"></i>';
        }

        return '<i class="text-danger glyphicon glyphicon-warning-sign"></i>';
    }
}
