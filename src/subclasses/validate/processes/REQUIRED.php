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
    
    public static function check(UME $ume, string $value, string $key, \stdClass $response): bool
    {
        if(!EX::empty($value))  { return true; }

        // 必須チェック
        if($conditions["require"] === false)    { return false; }

        $labels = $ume->get_labels();
        $label  = $labels["ja_JP"][$key] ?? $key;
        $response->VE[$key]     = "[$label] は必須項目です。" . $response->index;
        $response->has_error    = true;

        return false;
    }
    
}
