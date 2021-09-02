<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/21
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\basis\ume\UME;
use \mikisan\core\basis\ume\REQUIREMENT;
use \mikisan\core\exception\UMEException;

class MULTIPLE
{
    
    /**
     * 配列項目のバリデーション
     * 
     * @param   UME         $ume
     * @param   array       $data
     * @param   string      $type
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $resobj
     * @return  mixed
     * @throws  UMEException
     */
    public static function validate(UME $ume, $data, string $key, array $conditions, \stdClass $resobj)
    {
        $result = [];
        
        // 必須チェック
        if(!REQUIREMENT::should_validate($ume, $data, $key, $conditions, $resobj))
        {
            if($resobj->on_error)   { $resobj->has_error = true; }
            return $data;
        }
        
        if(!is_array($data))
        {
            $data_type  = gettype($data);
            throw new UMEException("バリデーションルール {$key} として渡された値が配列ではありません。[type: {$data_type}]");
        }
        
        $i = 0;
        foreach($data as $src)
        {
            $resobj->on_error   = false;
            
            $resobj->index      = ":{$i}";
            
            // バリデート
            $result[]   = ($conditions["method"] === UME::FILES)
                                ? FILE ::validate($ume, $src, $key, $conditions, $resobj)
                                : VALUE::validate($ume, $src, $key, $conditions, $resobj)
                                ;
            if($resobj->on_error)
            {
                $resobj->has_error  = true;
                $resobj->offset[]   = $i;
            }
            $i++;
        }
        return $result;
    }
    
}
