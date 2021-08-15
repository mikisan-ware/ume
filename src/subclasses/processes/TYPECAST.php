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
        switch(true)
        {
            case $type === UME::TYPE_INTEGER:   return (int)$value;
            case $type === UME::TYPE_REAL:      return (double)$value;
                
            case $type === UME::TYPE_STRING:    
            default:
                return (string)$value;
        }
    }
    
}
