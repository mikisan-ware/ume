<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/17
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\basis\ume\UME;
use \mikisan\core\basis\ume\UMESettings;

class NORMALIZE
{
    
    public static function conditino(array $conditions): array
    {
        $conditions["type"]             = $conditions["type"]           ?? "text";
        $conditions["auto_correct"]     = $conditions["auto_correct"]   ?? true;
        $conditions["filter"]           = $conditions["filter"]         ?? null;
        $conditions["trim"]             = $conditions["trim"]           ?? UME::TRIM_ALL;
        $conditions["null_byte"]        = $conditions["null_byte"]      ?? false;
        $conditions["method"]           = $conditions["method"]         ?? UMESetting::DEFAULT_METHOD;
        $conditions["require"]          = $conditions["type"]           ?? UMESetting::DEFAULT_REQUIRE;
        return $conditions;
    }
    
}
