<?php

/**
 * Project Name: mikisan-ware
 * Description : バリデーター
 * Start Date  : 2021/08/29
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\basis\ume\DATASOURCE;
use \mikisan\core\util\ex\EX;

class VALIDATE
{
    
    public static function do(UME $ume, string $key, array $conditions, \stdClass $response)
    {
        $matches    = [];
        (preg_match("/\A([^%]+)_(%_)*%(\[\])?\z/u", $key, $matches))            // 階層連番パラメター
                ? self::hierarchy($ume, $matches[1], $conditions, $response)
                : self::normal($ume, $key, $conditions, $response)
                ;
    }
    
    private static function normal(UME $ume, string $key, array $conditions, \stdClass $response)
    {
        // リクエスト取得
        $temp                   = DATASOURCE::get($conditions["method"], $key);
        $response->src[$key]    = $temp;
        
        $response->dist[$key]   = INDIVIDUAL::validate($ume, $temp, $key, $conditions, $response);
    }
    
    private static function hierarchy(UME $ume, string $prefix, array $conditions, \stdClass $response)
    {
        $result     = [];
        $all_data   = DATASOURCE::all($conditions["method"], $prefix);    // 指定されたmethodでのリクエストを全て取得
        //$response->src[$key]    = $temp;
        $VE         = [];

        foreach ($all_data as $key => $src)
        {
            // 階層連番パラメターではないルールは除外
            if (!preg_match("|\A{$prefix}_(\d+_)*\d+(\[\])?\z|u", $key))    { continue; }
            
            $serials    = self::get_serial_numbers($key, $prefix);
            $temp       = &$result;
            for($i = 0; $i < count($serials); $i++)
            {
                $idx    = $serials[$i];
                if($i === count($serials) - 1)
                {
                    $response->index    = ":" . implode(":", $serials);
                    $temp[$idx] = INDIVIDUAL::validate($ume, $src, $prefix, $conditions, $response);
                    if(!EX::empty($response->VE))
                    {
                        $VE[] = $response->VE[$prefix];
                        $response->VE[$prefix]  = null;
                    }
                }
                else
                {
                    if(!isset($temp[$idx])) { $temp[$idx] = []; }
                }
                $temp = &$temp[$idx];   // 配列を参照渡しで再定義
            }
            unset($temp);
        }
        
        $response->VE[$prefix]      = $VE;
        $response->dist[$prefix]    = $result;
    }
    
    private static function get_serial_numbers(string $key, string $prefix): array
    {
        $key    = preg_replace("/\A{$prefix}_/", "", $key);     // $keyから主キーを取り除く
        $key    = preg_replace("/\[\]\z/", "", $key);           // $keyから配列ブラケットを取り除く
        
        return array_map(function($i){ return (int)$i; }, explode("_", $key));  // 階層連番数字を配列に格納
    }
    
}
