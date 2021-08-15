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

use \mikisan\core\exception\UMEException;
use \mikisan\core\util\ex\EX;

class VALIDATOR
{
    
    public static function do(UME $ume, string $value, string $key, array $conditions, \stdClass $response): bool
    {
        $type   = $conditions["type"];
        $types  = $ume->get_types()[$type];
        $labels = $ume->get_labels();
        
        if(EX::empty($types["rule"]))   { return $value; }
        if(!is_callable($types["rule"]))
        {
            throw new UMEException("バリデーション定義 [{$conditions["type"]}] の rule は正しい callable として定義されていません。");
        }
        
        $result = $types["rule"]($value);
        if(!$result)
        {
            if(!is_callable($types["error"]))
            {
                throw new UMEException("バリデーション定義 [{$type}] の error は正しい callable　として定義されていません。");
            }
            
            $label  = $labels["ja_JP"][$key] ?? $key;
            
            $response->VE[$key]  = $types["error"]($label, $conditions);
        }
        
        return $result;
    }
    
}
