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
    
    private static function get_resobj(): \stdClass
    {
        $resobj             = new \stdClass();
        $resobj->has_error  = false;
        $resobj->on_error   = false;
        $resobj->VE         = [];
        $resobj->offset     = [];
        $resobj->index      = "";
        
        return $resobj;
    }
    
    public static function dataset(UME $ume, $src, string $prefix, string $key, array $conditions, \stdClass $response)
    {
        $response->src[$prefix]     = $src;
        $resobj                     = self::get_resobj();                           // バリデーション結果オブジェクトの初期化
        $response->dist[$prefix]    = INDIVIDUAL::validate($ume, $src, $key, $conditions, $resobj);
        self::set_response($response, $prefix, $resobj);
    }
    
    public static function normal(UME $ume, string $key, array $conditions, \stdClass $response)
    {
        $matches    = [];
        (preg_match("/\A([^%]+)_(%_)*%(\[\])?\z/u", $key, $matches))            // 階層連番パラメター
                ? self::hierarchy($ume, $matches[1], $conditions, $response)
                : self::simple($ume, $key, $conditions, $response)
                ;
    }
    
    private static function simple(UME $ume, string $key, array $conditions, \stdClass $response)
    {
        $matches    = [];
        $prefix     = (preg_match("|\A(.+)\[\]\z|u", $key, $matches)) ? $matches[1] : $key ;
        
        $src                        = DATASOURCE::get($conditions["method"], $prefix); // リクエスト取得
        $response->src[$prefix]     = $src;
        $resobj                     = self::get_resobj();                           // バリデーション結果オブジェクトの初期化
        $response->dist[$prefix]    = INDIVIDUAL::validate($ume, $src, $key, $conditions, $resobj);
        self::set_response($response, $prefix, $resobj);
    }
    
    private static function hierarchy(UME $ume, string $prefix, array $conditions, \stdClass $response)
    {
        $result     = [];
        $all_data   = DATASOURCE::all($conditions["method"], $prefix);          // 指定されたmethodでのリクエストを全て取得
        $VE         = [];

        foreach ($all_data as $key => $src)
        {
            // $prefix で始まる階層連番パラメタ以外は除外
            if (!preg_match("|\A{$prefix}_(\d+_)*\d+(\[\])?\z|u", $key))    { continue; }
            
            $response->src[$key]    = $src;                             // 元データ
            $resobj     = self::get_resobj();                           // バリデーション結果オブジェクトの初期化
            $serials    = self::get_serial_numbers($key, $prefix);      // 配列化された階層連番
            $temp       = &$result;
            for($i = 0; $i < count($serials); $i++)
            {
                $idx    = $serials[$i];
                if($i === count($serials) - 1)
                {
                    $resobj->index  = ":" . implode(":", $serials);
                    $temp[$idx]     = INDIVIDUAL::validate($ume, $src, $prefix, $conditions, $resobj);
                    self::set_response($response, $prefix, $resobj);
                }
                else
                {
                    if(!isset($temp[$idx])) { $temp[$idx] = []; }
                }
                $temp = &$temp[$idx];   // 配列を参照渡しで再定義
            }
            unset($temp);
        }
        
        $response->dist[$prefix]    = $result;
    }
    
    private static function set_response(\stdClass $response, string $prefix, \stdClass $resobj): void
    {

        if(!$resobj->has_error)  { return; }
        $response->has_error        = true;
        $response->VE[$prefix]      = (!isset($response->VE[$prefix]))
                                            ? $resobj->VE
                                            : array_merge($response->VE[$prefix], $resobj->VE)
                                            ;
        $response->offset[$prefix]  = (!isset($response->offset[$prefix]))
                                            ? $resobj->offset
                                            : array_merge($response->offset[$prefix], $resobj->offset)
                                            ;
    }
    
    /**
     * _ で繋がれた階層連番を explode() して配列にして返す
     * 
     * @param string $key
     * @param string $prefix
     * @return array
     */
    private static function get_serial_numbers(string $key, string $prefix): array
    {
        $key    = preg_replace("/\A{$prefix}_/", "", $key);     // $keyから主キーを取り除く
        $key    = preg_replace("/\[\]\z/", "", $key);           // $keyから配列ブラケットを取り除く
        
        return array_map(function($i){ return (int)$i; }, explode("_", $key));  // 階層連番数字を配列に格納
    }
    
}
