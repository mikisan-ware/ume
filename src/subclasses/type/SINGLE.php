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
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $response
     * @return  void
     * @throws  UMEException
     */
    public static function validate(UME $ume, string $key, array $conditions, \stdClass $response) : void
    {
        $should_validate    = true;
        $validate_result    = false;
        
        // バリデーションタイプ
        $type   = $conditions["type"];
        if(!isset($obj->types[$type]))
        {
            throw new UMEException("バリデーションタイプ [{$type}] は正しく定義されていません。");
        }
        
        // バリデート
        $value  = ($conditions["method"] === UME::FILES)
                            ? self::validate_value($ume, $value, $type, $key, $conditions, $response)
                            : self::validate_files($ume, $value, $type, $key, $conditions, $response)
                            ;
        
        $response->dest["key"]  = $value;
        
        return;
    }
    
    /**
     * $conditions["method"] => UME::FILES 以外のバリデート
     * 
     * @param   UME         $ume
     * @param   string      $type
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $response
     * @return  string
     */
    private static function validate_value(UME $ume, string $type, string $key, array $conditions, \stdClass $response): string
    {
        // リクエスト取得
        $src                    = DATASOURCE::get($conditions["method"], $key);
        $value                  = mb_convert_encoding((string)$src, UMESettings::ENCODE, $ume->from_encoding);
        $response->src["key"]   = $src;
        
        // フィルタ
        if(!EX::empty($conditions["filter"]))       { $value = FILTER::do($ume->get_filters(), $value, $conditions); }
        
        // オートコレクト
        if($conditions["auto_correct"] === true)    { $value = CORRECTOR::do($ume->get_types()[$type], $value, $conditions); }
        
        //NULLバイト除去
        if($conditions["null_byte"] === false)      { $value = self::strip_null($value); }
        
        // トリム
        $value = TRIM::do($value, $conditions);
        
        // 必須チェック
        if($conditions["require"] === true)         { $should_validate = REQUIRED::do($ume, $value, $key, $response); }
        
        // バリデーション
        if(($validate_result = VALIDATOR::do($ume, $value, $key, $conditions, $response)) === true)
        {
            // タイプキャスト
            $value = TYPECAST::do($ume->get_types()["type"], $value);
            
            // クローザー
            if(!EX::empty($conditions["closer"]))       { $value = CLOSER::do($ume->get_closers(), $value, $conditions); }
            
            // レンジチェック
            (isset($conditions["choice"]))
                    ? CHOICE::do($ume, $value, $key, $condition, $response)
                    : RANGE::do($ume, $value, $key, $condition, $response)
                    ;
        }
        
        return $value;
    }
    
    /**
     * $conditions["method"] => UME::FILES のバリデート
     * 
     * @param   UME         $ume
     * @param   string      $type
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $response
     * @return  array
     */
    private static function validate_files(UME $ume, string $type, string $key, array $conditions, \stdClass $response): array
    {
        // リクエスト取得
        $src                    = DATASOURCE::get($conditions["method"], $key);
        $value                  = mb_convert_encoding((string)$src, UMESettings::ENCODE, $ume->from_encoding);
        $response->src["key"]   = $src;
        
        $labels = $ume->get_labels();
        $label  = $labels["ja_JP"][$key] ?? $key;
        
        // アップロードファイルのセキュリティチェック
        FILECHECK::do($ume, $value, $key);
        
        // 拡張子チェック
        CHOICE::extdo($ume, $value, $key, $condition, $response);
        
        return $value;
    }
    
    /**
     * 文字列からnullバイトを除去する
     * 
     * @param   string      $value
     * @return  string
     */
    private static function strip_null(string $value): string
    {
        return str_replace("\0", "", $value);
    }
    
}
