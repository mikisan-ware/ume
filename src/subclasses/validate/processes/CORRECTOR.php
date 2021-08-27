<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/04
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\util\ex\EX;
use \mikisan\core\exception\UMEException;

class CORRECTOR
{
    
    public static function do(array $types, $value, array $conditions)
    {
        if(EX::empty($types["correct"]))    { return $value; }
        if(!is_callable($types["correct"]))
        {
            throw new UMEException("バリデーション定義 [{$conditions["type"]}] の correct は正しい callable として定義されていません。");
        }
        
        return $types["correct"]($value);
    }
    
}
