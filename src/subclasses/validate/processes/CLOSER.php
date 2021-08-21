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

class CLOSER
{
    
    public static function do(array $closers, string $value, array $conditions)
    {
        if(!isset($conditions["closer"]))   { return $value; }
        $applys    = explode("|", $conditions["closer"]);
        
        foreach($applys as $apply)
        {
            if(!isset($closers[$apply]))  { continue; }
            $value  = $closers[$apply]($value);
        }
        return $value;
    }
    
}
