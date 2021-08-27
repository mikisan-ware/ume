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
     * @param   \stdClass   $response
     * @return  mixed
     * @throws  UMEException
     */
    public static function validate(UME $ume, $src, string $key, array $conditions, \stdClass $response)
    {
        $response->on_error     = false;
        
        // バリデート
        return ($conditions["method"] === UME::FILES)
                    ? FILE ::validate($ume, $src, $key, $conditions, $response)
                    : VALUE::validate($ume, $src, $key, $conditions, $response)
                    ;
    }
    
}
