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
use \mikisan\core\util\mime\MIME;

class DATASOURCE
{
    
    /**
     * データの取得
     */
    public static function get(UME $ume, string $target_method, mixed $key)
    {
        $method = strtoupper($target_method);
        
        switch (true)
        {
            case $method === UME::POST:     return $_POST[$key]         ?? null;
            case $method === UME::GET:      return $_GET[$key]          ?? null;
            case $method === UME::COOKIE:   return $_COOKIE[$key]       ?? null;
            case $method === UME::RESTful:  return Router::route()->params[$key]    ?? null;
            case $method === UME::ARGS:     return Router::route()->args[$key]      ?? null;
            case $method === UME::DATASET:  return $ume->get_dataset()  ?? null;
                
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
                
                return $params[$key] ?? null;

            case $method === UME::FILES:
            
                $file   = (isset($_FILES[$key])) ? $_FILES[$key] : null;
                if(!isset($file["name"]))   { return null; }
                
                return (is_array($file["tmp_name"]))
                            ? self::rebuild_file_info($file)                    // multiple の場合は、配列を再構築する
                            : self::set_real_mime_type($file)                   // 単一ファイルの場合はマイムタイプを調べセット
                            ;
            default:
                
                throw new UMEException("バリデーション設定内に記述された、キー [{$key}] の method [{$target_method}] は定義されていません。");
        }
    }
    
    /**
     * $_FILES を、ファイル毎の要素に組み替える
     * 
     * @param   array   $files
     * @return  array
     */
    private static function rebuild_file_info(array $files): array
    {
        $tmp    = [];
        foreach($files["name"] as $idx => $val)
        {
            $f              = [];
            $f["name"]      = $files["name"][$idx];
            $f["type"]      = $files["type"][$idx];
            $f["real_type"] = (!empty($files["tmp_name"][$idx])) ? MIME::getMIME($files["tmp_name"][$idx]) : "";
            $f["tmp_name"]  = $files["tmp_name"][$idx];
            $f["error"]     = $files["error"][$idx];
            $f["size"]      = $files["size"][$idx];
            $tmp[]          = $f;
        }
        return $tmp;
    }
    
    /**
     * ファイルを検査して正しいMIME-Typeをセットする
     * 
     * @param   array   $file
     * @return  array
     */
    private static function set_real_mime_type(array $file): array
    {
        $file["real_type"]  = (!empty($file["tmp_name"]))? MIME::getMIME($file["tmp_name"]) : "";
        return $file;
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
