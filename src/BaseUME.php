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

use \mikisan\core\basis\ume\UMESettings;
use \mikisan\core\exception\UMEException;
use \mikisan\core\basis\ume\NORMALIZE;

interface UME
{
    const   TRIM_N = false, TRIM_NONE = false, 
            TRIM_L = 1, TRIM_LEFT = 1,
            TRIM_R = 2, TRIM_RIGHT = 2, 
            TIMR_A = true, TRIM_ALL = true;
    
    const   Base64 = 0b0001, HTML = 0b0010;
    
    const   GET = "GET", POST = "POST", DELETE = "DELETE", PUT = "PUT", PATCH = "PATCH"; 
    const   OPTIONS = "OPTIONS", HEAD = "HEAD", LINK = "LINK", ULINK = "ULINK", TRACE = "TRACE";
    const   RESTful = "RESTFUL", ARGS = "ARGS", DATASET = "DATASET";
    
    const   REQUEST = "REQUEST", COOKIE = "COOKIE", FILES = "FILES";
    
    const   TYPE_STRING = 0, TYPE_INTEGER = 1, TYPE_REAL = 2, TYPE_FILE = 3;
}

abstract class BaseUME implements UME
{
    
    protected $types    = [];
    protected $filters  = [];
    protected $closers  = [];
    protected $rules    = [];
    protected $labels   = [];
    protected $dataset  = null;
    protected $from_encoding    = "UTF-8";
    
    // バリデーション定義で許可されている連想配列キー
    private $allowed_keys   = ["name", "type", "min", "max", "choice", "auto_correct", "filter", "trim", "null_byte", "method", "index", "require"];
    
    public function __construct()
    {
        // デフォルトバリデーション定義の登録
        $this->register_types(UMESettings::types());
        
        // バリデーションフィルタの登録
        $this->register_filters(UMESettings::filters());
        
        // バリデーションクローザーの登録
        $this->register_closers(UMESettings::closers());
        
        // バリデーションルールの登録
        if(method_exists($this, "rules"))
        {
            foreach($this->rules() as $condition)
            {
                if(!isset($this->types[$condition["type"]]))
                {
                    throw new UMEException("バリデーションタイプ [{$condition["type"]}] は定義されていません。");
                }
            }
            
            $this->register_rules($this->rules());
        }
        
        // I18Nラベルの登録
        if(method_exists($this, "labels"))
        {
            $this->register_labels($this->labels());
        }
    }
    
    public function set_from_encoding($value): void { $this->from_encoding = $value; }
    public function get_from_encoding(): string     { return $this->from_encoding; }
    
    /**
     * デフォルトバリデーション定義の登録・ゲッター
     * 
     * @param   array   $types
     * @return  \mikisan\core\basis\ume\UME
     */
    protected function register_types(array $types): UME
    {
        $this->types = array_merge($this->types, $types);
        
        return $this;
    }
    
    public function get_types(): array
    {
        return $this->types;
    }
    
    /**
     * バリデーションフィルタの登録・ゲッター
     * 
     * @param   array   $filters
     * @return  \mikisan\core\basis\ume\UME
     */
    protected function register_filters(array $filters): UME
    {
        $this->filters  = array_merge($this->filters, $filters);
        
        return $this;
    }
    
    public function get_filters(): array
    {
        return $this->filters;
    }
    
    /**
     * バリデーションクローザーの登録・ゲッター
     * 
     * @param   array   $closers
     * @return  \mikisan\core\basis\ume\UME
     */
    protected function register_closers(array $closers): UME
    {
        $this->closers  = array_merge($this->closers, $closers);
        
        return $this;
    }
    
    public function get_closers(): array
    {
        return $this->closers;
    }
    
    /**
     * バリデーションルールの登録・ゲッター
     * 
     * @param   array   $rules
     * @return  \mikisan\core\basis\ume\UME
     */
    protected function register_rules(array $rules): UME
    {
        $this->rules = array_merge($this->rules, $rules);
        
        return $this;
    }
    
    public function get_rules(): array
    {
        return $this->rules;
    }
    
    /**
     * I18Nラベルの登録・ゲッター
     * 
     * @param   array   $labels
     * @return  \mikisan\core\basis\ume\UME
     */
    protected function register_labels(array $labels): UME
    {
        $this->labels = array_merge($this->labels, $labels);
        
        return $this;
    }
    
    public function get_labels(): array
    {
        return $this->labels;
    }
    
    /**
     * データセットの登録・ゲッター
     * 
     * @param   array   $dataset
     * @return  \mikisan\core\basis\ume\UME
     */
    protected function register_dataset(array $dataset): UME
    {
        if(!is_array($src))
        {
            $data_type  = gettype($dataset);
            throw new UMEException("UME::register_dataset() に渡された入力値が配列ではありません。[type: {$data_type}]");
        }
        
        $this->dataset  = $dataset;
        
        return $this;
    }
    
    public function get_dataset(): array
    {
        return $this->dataset;
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
    
    public function validate(Dto $dto): UME
    {
        $response               = new \stdClass();
        $response->has_error    = false;
        $response->index        = "";
        $response->VE           = [];
        $response->src          = [];
        $response->dist         = [];
        
        foreach($this->rules as $key => $conditions)
        {
            // リクエスト取得
            $src    = DATASOURCE::get($ume, $conditions["method"], $key);
            $response->src["key"]   = $src;
            
            // バリデートコンディションの正規化
            $conditions = self::normarize_condition($conditions);
            
            switch(true)
            {
                case preg_match("|\A([^%]+)_(%_)*%(\[\])?\z|u", $key):
                    
                    $value  = HIERARCHY::validate($this, $type, $key, $conditions, $response);    // 階層連番項目のバリデート
                    break;
                
                case preg_match("|\A.+\[\]\z|u", $key):
                    
                    $value  = MULTIPLE::validate($this, $src, $key, $conditions, $response);     // 配列項目のバリデート
                    break;
                    
                default:
                    
                    $value  = SINGLE::validate($this, $src, $key, $conditions, $response);       // 単一項目のバリデート
            }
            
            $response->dest["key"]  = $values;
        }
    }
    
    private static function normarize_condition(array $conditions): array
    {
        $conditions["type"]             = $conditions["type"]           ?? "text";
        $conditions["auto_correct"]     = $conditions["auto_correct"]   ?? true;
        $conditions["filter"]           = $conditions["filter"]         ?? null;
        $conditions["trim"]             = $conditions["trim"]           ?? UME::TRIM_ALL;
        $conditions["null_byte"]        = $conditions["null_byte"]      ?? false;
        $conditions["method"]           = $conditions["method"]         ?? UMESetting::DEFAULT_METHOD;
        $conditions["require"]          = $conditions["type"]           ?? UMESetting::DEFAULT_REQUIRE;
        return $conditions;
    }
    
}
