<?php

/**
 * Project Name: mikisan-ware
 * Description : バリデーター
 * Start Date  : 2021/07/27
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\util\router\Router;
use \mikisan\core\exception\UMEException;

class DATASOURCE
{
    
    /**
     * データの取得
     */
    public static function get(string $target_method, mixed $key)
    {
        $method = strtoupper($target_method);
        
        switch (true)
        {
            case $method === UME::POST:     return $_POST[$key]     ?? UMESettings::EMPTY_VALUE;
            case $method === UME::GET:      return $_GET[$key]      ?? UMESettings::EMPTY_VALUE;
            case $method === UME::COOKIE:   return $_COOKIE[$key]   ?? UMESettings::EMPTY_VALUE;
            case $method === UME::RESTful:  return Router::route()->params[$key]    ?? UMESettings::EMPTY_VALUE;
            case $method === UME::ARGS:     return Router::route()->args[$key]      ?? UMESettings::EMPTY_VALUE;
                
                
            // ↓ 未Test
            case $method === UME::DATASET:  return $this->dataset[$key]             ?? UMESettings::EMPTY_VALUE;
                
            case $method === UME::HEAD:
            case $method === UME::OPTIONS:
            case $method === UME::PUT:
            case $method === UME::PATCH:
            case $method === UME::DELETE:
            case $method === UME::TRACE:
            case $method === UME::LINK:
            case $method === UME::ULINK:

                $params = [];
                parse_str(file_get_contents("php://input"), $params);
                
                return isset($params[$key]) ? $params[$key] : UMESettings::EMPTY_VALUE;

            case $method === UME::FILES:
            
                $file   = (isset($_FILES[$key])) ? $_FILES[$key] : null;
                if(isset($file["name"]))
                {
                    if(is_array($file["name"]))
                    {
                        // multipleの場合は、配列を再構築する
                        $file = $this->rebuildFileInfoArray($file);
                    }
                    else
                    {
                        // singleファイルの場合はマイムタイプを調べセット
                        $file["real_type"]  = (!empty($file["tmp_name"]))? MIME::getMIME($file["tmp_name"]) : "";
                    }
                }
                
                return $file;
                
            default:
                
                throw new UMEException("バリデーション設定内に記述された、キー [{$key}] の method [{$target_method}] は定義されていません。");
        }
    }
    
    public static function all(string $key, array $conditions)
    {
        $method = strtoupper($conditions["method"]);
        
        switch (true)
        {
            case $method === UME::POST:         return $_POST;
            case $method === UME::GET:          return $_GET;
            case $method === UME::COOKIE:       return $_COOKIE;
            case $method === UME::FILES:        return $_FILES;
            //
            case $method === UME::RESTful:      return DATASTORE::instance()->route->params;
            //
            case $method === UME::HEAD:
            case $method === UME::OPTIONS:
            case $method === UME::PUT:
            case $method === UME::PATCH:
            case $method === UME::DELETE:
            case $method === UME::TRACE:
            case $method === UME::LINK:
            case $method === UME::ULINK:
                
                $params = [];
                parse_str(file_get_contents("php://input"), $params);
                
                return $params;
                
            default:
                
                throw new UMEException(get_class($this) ." のバリデーション設定内に記述された、キー {$key} の method の値が不正です。[{$conditions["method"]}]");
        }
    }
    
}
