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

use \mikisan\core\basis\dto\Dto;
use \mikisan\core\basis\ume\UMESettings;
use \mikisan\core\exception\UMEException;

interface UME
{
    const   TRIM_N = false, TRIM_NONE = false, 
            TRIM_L = 1, TRIM_LEFT = 1,
            TRIM_R = 2, TRIM_RIGHT = 2, 
            TIMR_A = true, TRIM_ALL = true;
    
    const   Base64 = 0b0001, HTML = 0b0010;
    
    const   GET = "GET", POST = "POST", DELETE = "DELETE", PUT = "PUT", PATCH = "PATCH"; 
    const   OPTIONS = "OPTIONS", HEAD = "HEAD", LINK = "LINK", ULINK = "ULINK", TRACE = "TRACE";
    const   RESTful = "RESTFUL", DATASET = "DATASET";
    
    const   REQUEST = "REQUEST", COOKIE = "COOKIE", FILES = "FILES";
}

abstract class BaseUME implements UME
{
    
    protected   $types = [];
    
    // バリデーション定義で許可されている連想配列キー
    private     $allowed_keys   = ["name", "type", "min", "max", "choice", "auto_correct", "trim", "null_byte", "method", "index", "require"];
    
    public function __construct(Dto $dto)
    {
        // デフォルトバリデーション定義の登録
        $this->register_types(UMESettings::types());
    }
    
    public function register_types(array $types): UME
    {
        foreach($types as $key => $setting)
        {
            $this->types[$key] = $setting;
        }
    }
    
    /**
     * $conditionsに、許可されていないキーが使用されている場合はExceptionを投げる
     * 
     * @param   array           $conditions
     * @throws  UMEException
     */
    private function validate_condition(string $rule, array $conditions)
    {
        foreach($conditions as $key => $value)
        {
            if(!in_array($key, self::$allowed_keys, true))
            {
                throw new UMEException("バリデーションルール {$rule} の設定に、許可されていないキー {$key} が使用されています。");
            }
        }
    }
    
    public function validate(): UME
    {     
        foreach($this->validators as $key => $conditions)
        {
            $this->validate_condition($key, $condigions);
            
            switch(true)
            {
                case preg_match("|\A([^%]+)_(%_)*%(\[\])?\z|u", $key):
                    
                    HIERARCHY::parse(self::$dto, $key, $conditions);   // 階層連番パラメター
                    break;
                
                case preg_match("|\A.+\[\]\z|u", $key):
                    
                    $this->type_array(self::$dto, $key, $conditions);  // 配列
                    break;
                    
                default:
                    
                    NORMAL::parse(self::$dto, $key, $conditions);      // 通常パラメター取得
            }
        }
    }
    
    /**
     * ハイフン相当文字の半角ハイフン化
     * 
     * @param type $req
     * @return type
     */
    public function adjustHyphen(string $req) : string
    {
        $req = mb_ereg_replace("ー", "-", $req);
        $req = mb_ereg_replace("ｰ", "-", $req);
        $req = mb_ereg_replace("―", "-", $req);
        $req = mb_ereg_replace("－", "-", $req);
        $req = mb_ereg_replace("‐", "-", $req);
        return $req;
    }
}
