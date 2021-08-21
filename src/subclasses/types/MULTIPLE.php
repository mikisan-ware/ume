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
use \mikisan\core\basis\ume\DATASOURCE;

class MULTIPLE
{
    
    /**
     * 配列項目のバリデート
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
    public static function validate(UME $ume, $src, string $type, string $key, array $conditions, \stdClass $response) : array
    {
        // リクエスト取得
        $src            = DATASOURCE::get($conditions["method"], $key);
        $response->src["key"]   = $src;
        
        // バリデート
        switch(true)
        {
            case $conditions["method"] === UME::DATASET:
                
                $values = $src;
                $i      = 0;
                foreach($values as &$data)
                {
                    $i++;
                    $response->index    = "[データ番号: {$i}]";
                    $data[$key]         = self::do($ume, $data[$key], $type, $key, $conditions, $response);
                }
                unset($data);

            default:
                
                $response->index    = "";
                $values = self::do($ume, $src, $type, $key, $conditions, $response);
        }

        return $values;
    }
    
    private static function do(UME $ume, $src, string $type, string $key, array $conditions, \stdClass $response)
    {
        $labels     = $ume->get_labels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        
        // 必須チェック
        if(!self::has_values($ume, $src, $label, $key, $conditions, $response)) { return null; }
        
        $values = $src;
        
        // 配列要素毎のバリデート
        $i = 0;
        foreach($values as $idx => &$value)
        {
            $i++;
            $response->index    .= (is_int($idx)) ? "[要素: {$i}]" : "[要素: {$label}" ;
            
            $value      = ($conditions["method"] === UME::FILES)
                                ? FILE ::validate($ume, $value, $type, $key, $conditions, $response)
                                : VALUE::validate($ume, $value, $type, $key, $conditions, $response)
                                ;
        }
        unset($value);
        
        return $values;
    }
    
    /**
     * タイプチェックを行い、配列として返す
     * 
     * @param   UME     $ume
     * @param   mixed   $src
     * @param   string  $label
     * @param   string  $key
     * @return  array
     * @throws  UMEException
     */
    private static function has_values(UME $ume, $src, string $label, string $key, array $conditions, \stdClass $response): bool
    {
        if(!is_array($src))
        {
            $data_type  = gettype($src);
            throw new UMEException("[{$label}] でリクエストされた入力値が配列ではありません。[type: {$data_type}]");
        }
        if($conditions["require"] === true && EX::empty($src))
        {
            $response->VE[$key]     = "[$label] は必須項目です。" . $response->index;;
            $response->has_error    = true;
            
            return false;
        }
        
        return true;
    }
    
}
