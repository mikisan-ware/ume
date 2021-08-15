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

class NORMAL
{
    
    public static function validate(UME $ume, string $key, array $conditions, \stdClass $response) : void
    {
        // リクエスト取得
        $value                  = DATASOURCE::get($conditions["method"], $key);
        $response->src["key"]   = $value;
        
        // バリデーションタイプ
        $type   = $conditions["type"];
        if(!isset($obj->types[$type]))
        {
            throw new UMEException("バリデーションタイプ [{$type}] は正しく定義されていません。");
        }
        $setting    = $obj->types[$type];
        
        // フィルタ
        if(!EX::empty($conditions["filter"]))       { $value = FILTER::do($ume->get_filters(), $value, $conditions); }
        
        // オートコレクト
        if($conditions["auto_correct"] === true)    { $value = CORRECTOR::do($ume->get_types()[$type], $value, $conditions); }
        
        // バリデーション
        $result = VALIDATOR::do($ume, $value, $key, $conditions, $response);
        
        // タイプキャスト
        if($result === true)                        { $value = TYPECAST::do($ume->get_types()["type"], $value); }
        
        // クローザー
        if(!EX::empty($conditions["closer"]))       { $value = CLOSER::do($ume->get_closers(), $value, $conditions); }
        
        $response->dest["key"]  = $value;
        
        /*
        if($conditions["method"] === UME::FILES)
        {
            $dto->setPineUploads($key, $value);
        }
         */
    }
    
}
