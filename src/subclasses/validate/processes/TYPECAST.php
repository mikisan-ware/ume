<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/15
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\basis\ume\UME;

class TYPECAST
{
    
    public static function do(int $type, $value)
    {
        if(is_null($value)) { return null; }
        
        switch(true)
        {
            case $type === UME::TYPE_INTEGER:   return self::cast_int($value);
            case $type === UME::TYPE_REAL:      return self::cast_real($value);
                
            case $type === UME::TYPE_STRING:    
            default:
                return (string)$value;
        }
    }
    
    private static function cast_int($value): int
    {
        if(EX::empty($value))   { return null; }
        return (int)$value;
    }
    
    private static function cast_real($value): float
    {
        if(EX::empty($value))   { return null; }
        return (double)$value;
    }
    
}
