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

class FILTER
{
    
    public static function do(array $filters, $value, array $conditions)
    {
        if(!isset($conditions["filter"]))   { return $value; }
        $applys    = explode("|", $conditions["filter"]);
        
        foreach($applys as $apply)
        {
            if(!isset($filters[$apply]))  { continue; }
            $value  = $filters[$apply]($value);
        }
        return $value;
    }
    
}
