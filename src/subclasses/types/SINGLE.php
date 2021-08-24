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
use \mikisan\core\basis\ume\DATASOURCE;

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
     * @return  void
     * @throws  UMEException
     */
    public static function validate(UME $ume, $src, string $key, array $conditions, \stdClass $response)
    {
        // バリデート
        switch(true)
        {
            case $conditions["method"] === UME::FILES:
                
                $value  = FILE ::validate($ume, $src, $type, $key, $conditions, $response);
                
            case $conditions["method"] === UME::DATASET:
                
                $value  = $src;
                
                $i  = 0;
                foreach($value as &$data)
                {
                    $i++;
                    $response->index    = "[データ番号: {$i}]";
                    $data[$key] = VALUE::validate($ume, $data[$key], $type, $key, $conditions, $response);
                }
                unset($data);

            default:
                
                $value  = VALUE::validate($ume, $value, $key, $conditions, $response);
        }
        
        return $value;
    }
    
}
