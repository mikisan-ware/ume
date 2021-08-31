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
    const   FETCH_NORMAL = 0, FETCH_DATASET = 1;
    
    const   TRIM_N = false, TRIM_NONE = false, 
            TRIM_L = 1, TRIM_LEFT = 1,
            TRIM_R = 2, TRIM_RIGHT = 2, 
            TIMR_A = true, TRIM_ALL = true;
    
    const   Base64 = 0b0001, HTML = 0b0010;
    
    const   GET = "GET", POST = "POST", DELETE = "DELETE", PUT = "PUT", PATCH = "PATCH"; 
    const   OPTIONS = "OPTIONS", HEAD = "HEAD", LINK = "LINK", ULINK = "ULINK", TRACE = "TRACE";
    const   RESTful = "RESTFUL", REST = self::RESTful;
    const   ARGS = "ARGS", DATASET = "DATASET";
    
    const   REQUEST = "REQUEST", COOKIE = "COOKIE", FILES = "FILES", FILE = self::FILES;
    
    const   TYPE_STRING = 0, TYPE_INTEGER = 1, TYPE_REAL = 2, TYPE_FILE = 3;
    
    const   DATA_SIMPLE = 0, DATA_MULTI = 1;
}

abstract class BaseUME implements UME
{
    
    protected $result       = null;
    protected $types        = [];
    protected $filters      = [];
    protected $closers      = [];
    protected $rules        = [];
    protected $labels       = [];
    protected $fetch_mode   = UME::FETCH_NORMAL;
    protected $dataset      = null;
    protected $from_encoding    = "UTF-8";
    
    // バリデーション定義で許可されている連想配列キー
    private $allowed_keys   = [
                                "name", "type", "min", "max", "choice", "auto_correct", 
                                "filter", "closer", "trim", "null_byte", "method", "require"
                            ];
    
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
            foreach($this->rules() as $rule => $conditions)
            {
                $this->check_condition($rule, $conditions);
            }
            $this->register_rules($this->rules());
        }
        
        // I18Nラベルの登録
        if(method_exists($this, "labels"))
        {
            $this->register_labels($this->labels());
        }
    }
    
    /**
     * $conditionsに、許可されていないキーが使用されている場合はExceptionを投げる
     * 
     * @param   array           $conditions
     * @throws  UMEException
     */
    private function check_condition(string $rule, array $conditions)
    {
        foreach($conditions as $key => $value)
        {
            if(!in_array($key, $this->allowed_keys, true))
            {
                throw new UMEException("バリデーションルール {$rule} の設定に、許可されていないキー {$key} が使用されています。");
            }
        }
    }
    
    public function setFromEncoding($value): UME
    {
        $this->from_encoding = $value;
        return $this;
    }
    public function getFromEncoding(): string       { return $this->from_encoding; }
    
    public function getResult(): \stdClass          { return $this->result; }
        
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
    
    public function getTypes(): array
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
    
    public function getFilters(): array
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
    
    public function getClosers(): array
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
    
    public function getRules(): array
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
    
    public function getLabels(): array
    {
        return $this->labels;
    }
    
    /**
     * データセットの登録・ゲッター
     * 
     * @param   array   $dataset
     * @return  \mikisan\core\basis\ume\UME
     */
    public function dataset($dataset): UME
    {
        if(!is_array($dataset))
        {
            $data_type  = gettype($dataset);
            throw new UMEException("UME::dataset() に渡された入力値が配列ではありません。[type: {$data_type}]");
        }
        
        $this->fetch_mode   = UME::FETCH_DATASET;
        $this->dataset      = $dataset;
        
        return $this;
    }
    
    public function getDataset(): array
    {
        return $this->dataset;
    }
    
    private function get_response_set(int $type = UME::DATA_SIMPLE): \stdClass
    {
        $response               = new \stdClass();
        $response->type         = $type;
        $response->has_error    = false;
        $response->info         = [];
        $response->data         = [];
        
        return $response;
    }
    
    private function get_response(): \stdClass
    {
        $response               = new \stdClass();
        $response->has_error    = false;
        $response->on_error     = false;
        $response->VE           = [];
        $response->offset       = [];
        $response->src          = [];
        $response->dist         = [];
        $response->index        = "";
        
        return $response;
    }
    
    public function validate(): UME
    {
        $this->result   =($this->fetch_mode === UME::FETCH_DATASET)
                                ? $this->fetch_dataset()
                                : $this->fetch_normal()
                                ;
        return $this;
    }
    
    /**
     * データセットのバリデーション
     * 
     * @param   \stdClass   $response
     */
    private function fetch_dataset()
    {
        $response_set   = $this->get_response_set(UME::DATA_MULTI);
        
        $i = 0;
        foreach($this->dataset as $row)
        {
            $response   = $this->get_response();
            foreach($this->rules as $key => $conditions)
            {
                $response->src[$key]    = $row[$key];
                
                // バリデートコンディションの正規化
                $conditions             = $this->normarize_condition($conditions);
                
                // バリデーション
                $response->dist[$key]   = INDIVIDUAL::validate($this, $row[$key], $key, $conditions, $response);
            }
            
            // バリデーション情報の統合
            if($response->has_error)    { $response_set->has_error = true; }
            $response_set->info[$i]             = new \stdClass();
            $response_set->info[$i]->has_error  = $response->has_error;
            $response_set->info[$i]->error      = $response->VE;
            $response_set->info[$i]->offset     = $response->offset;
            $response_set->info[$i]->src        = $response->src;
            $response_set->data[$i]             = $response->dist;
            
            $i++;
        }
        
        return $response_set;
    }
    
    /**
     * 通常リクエストデータのバリデーション
     * 
     * @param \stdClass $response
     */
    private function fetch_normal()
    {
        $response_set   = $this->get_response_set(UME::DATA_SIMPLE);
        $response       = $this->get_response();
        
        foreach($this->rules as $key => $conditions)
        {
            VALIDATE::do($this, $key, $this->normarize_condition($conditions), $response);
        }
        
        // バリデーション情報の統合
        if($response->has_error)    { $response_set->has_error = true; }
        $response_set->info             = new \stdClass();
        $response_set->info->has_error  = $response->has_error;
        $response_set->info->error      = $response->VE;
        $response_set->info->offset     = $response->offset;
        $response_set->info->src        = $response->src;
        $response_set->data             = $response->dist;
        
        return $response_set;
    }
    
    /**
     * バリデートコンディションの正規化
     * 
     * @param   array   $conditions
     * @return  array
     */
    private static function normarize_condition(array $conditions): array
    {
        $conditions["type"]             = $conditions["type"]           ?? "text";
        $conditions["auto_correct"]     = $conditions["auto_correct"]   ?? true;
        $conditions["filter"]           = $conditions["filter"]         ?? null;
        $conditions["trim"]             = $conditions["trim"]           ?? UME::TRIM_ALL;
        $conditions["null_byte"]        = $conditions["null_byte"]      ?? false;
        $conditions["method"]           = $conditions["method"]         ?? UMESetting::DEFAULT_METHOD;
        $conditions["require"]          = $conditions["require"]        ?? UMESetting::DEFAULT_REQUIRE;
        
        return $conditions;
    }
    
}
