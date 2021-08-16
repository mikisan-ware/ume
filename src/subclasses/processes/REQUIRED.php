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

use \mikisan\core\exception\UMEException;
use \mikisan\core\util\ex\EX;

class REQUIRED
{
    
    public static function do(UME $ume, string $value, string $key, \stdClass $response): bool
    {
        $labels = $ume->get_labels();
        
        if(EX::empty($value))
        {
            $label  = $labels["ja_JP"][$key] ?? $key;
            
            $response->VE[$key]  = "[$label] は必須項目です。";
            
            return false;
        }
        
        return true;
    }
    
}
