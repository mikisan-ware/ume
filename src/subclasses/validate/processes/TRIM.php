<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/16
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

class TRIM
{
    
    public static function do(UME $ume, string $value, string $key, \stdClass $response)
    {
        $trim   = $conditions["trim"];
        switch(true)
        {
            case $trim === UME::TRIM_ALL:   return trim($value);
            case $trim === UME::TRIM_LEFT:  return ltrim($value);
            case $trim === UME::TRIM_RIGHT: return rtrim($value);
            case $trim === UME::TRIM_NONE:
                return $value;
        }
    }
    
}
