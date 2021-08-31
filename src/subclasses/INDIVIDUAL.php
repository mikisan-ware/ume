<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/29
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\basis\ume\UME;

class INDIVIDUAL
{
    
    /**
     * ルール毎のバリデーション
     * 
     * @param   UME         $ume
     * @param   mixed       $src
     * @param   mixed       $key            string|int(index)
     * @param   array       $conditions
     * @param   \stdClass   $response
     * @return  mixed
     */
    public static function validate(UME $ume, $src, $key, array $conditions, \stdClass $response)
    {
        switch(true)
        {
            case preg_match("|\A.+\[\]\z|u", $key):

                return MULTIPLE::validate($ume, $src, $key, $conditions, $response);       // 配列項目のバリデート

            default:
                
                return SINGLE::validate($ume, $src, $key, $conditions, $response);         // 単一項目のバリデート
        }
    }
    
}
