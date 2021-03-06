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

use \mikisan\core\basis\ume\SELECTOR;
use \mikisan\core\basis\ume\RESULT;
use \mikisan\core\exception\UMEException;
use \mikisan\core\util\ex\EX;

class VALIDATOR
{
    
    public static function do(UME $ume, string $value, string $key, array $conditions, \stdClass $resobj): bool
    {
        $type   = $conditions["type"];
        $types  = $ume->getTypes()[$type];
        
        if(EX::empty($types["rule"]))   { return $value; }
        if(!is_callable($types["rule"]))
        {
            throw new UMEException("バリデーション定義 [{$conditions["type"]}] の rule は正しい callable として定義されていません。");
        }
        
        // バリデーション実行
        $result = $types["rule"]($value);
        if(!is_bool($result))
        {
            $data_type  = gettype($result);
            throw new UMEException("バリデーション定義 [{$conditions["type"]}] の rule の返り値が bool 型ではありません。[type: {$data_type}]");
        }
        
        if($result) { return true; }
        
        if(!is_callable($types["error"]))
        {
            throw new UMEException("バリデーション定義 [{$type}] の error は正しい callable　として定義されていません。");
        }
        
        // バリデーションエラーメッセージ
        $label              = SELECTOR::getLabel($ume, $key, $resobj);
        $resobj->VE[]       = $types["error"]($label, $conditions);
        $resobj->on_error   = true;
        
        return false;
    }
    
}
