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

use \mikisan\core\exception\UMEException;

class DATASOURCE
{
    
    /**
     * データの取得
     * 
     * @param   \mikisan\core\util\ume\Dto  $dto
     * @param   string                      $key
     * @param   array                       $conditions
     * @return  type
     * @throws  ValidationErrorException
     */
    public static function get(Dto $dto, string $key, array $conditions)
    {
        $method = strtoupper($conditions["method"]);
        
        switch (true)
        {
            case $method === UME::POST:     return $_POST[$key]     ?? $dto->settings->empty_value;
            case $method === UME::GET:      return $_GET[$key]      ?? $dto->settings->empty_value;
            case $method === UME::COOKIE:   return $_COOKIE[$key]   ?? $dto->settings->empty_value;
            
            case $method === UME::FILES:
            
                $req = (isset($_FILES[$key])) ? $_FILES[$key] : null;
                if(isset($req["name"]))
                {
                    if(is_array($req["name"]))
                    {
                        // multipleの場合は、配列を再構築する
                        $req = $this->rebuildFileInfoArray($req);
                    }
                    else
                    {
                        // singleファイルの場合はマイムタイプを調べセット
                        $req["real_type"]  = (!empty($req["tmp_name"]))? MIME::getMIME($req["tmp_name"]) : "";
                    }
                }
                
                return $req;
                
            case $method === UME::RESTful:
                
                return DATASTORE::instance()->route->params[(int)$conditions["index"]];
                
            case $method === UME::DATASET:
                
                return $this->dataset[(int)$conditions["index"]];
                
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
                
                return isset($params[$key]) ? $params[$key] : $this->empty_param_value;
                
            default:
                
                throw new UMEException(get_called_class() . " のバリデーション設定内に記述された、キー {$key} の method の値が不正です。[{$conditions["method"]}]");
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
