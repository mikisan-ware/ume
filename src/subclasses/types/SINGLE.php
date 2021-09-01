<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/07/27
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\basis\ume\UME;

class SINGLE
{
    
    /**
     * 単一項目のバリデーション
     * 
     * @param   UME         $ume
     * @param   mixed       $src
     * @param   string      $type
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $resobj
     * @return  mixed
     * @throws  UMEException
     */
    public static function validate(UME $ume, $src, string $key, array $conditions, \stdClass $resobj)
    {
        $resobj->on_error     = false;
        
        // 必須チェック
        if(!REQUIREMENT::should_validate($ume, $src, $key, $conditions, $resobj))
        {
            if($resobj->on_error)   { $resobj->has_error = true; }
            return ($resobj->has_error) ? null : $src ;
        }
        
        // バリデート
        $result =  ($conditions["method"] === UME::FILES)
                        ? FILE ::validate($ume, $src, $key, $conditions, $resobj)
                        : VALUE::validate($ume, $src, $key, $conditions, $resobj)
                        ;
        if($resobj->on_error)
        {
            $resobj->has_error  = true;
        }
        
        return $result;
    }
    
}
