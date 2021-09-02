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

namespace mikisan\pine\app;

use \mikisan\core\basis\ume\UME;

class ChildUME extends ModuleCommonUME
{
    public function __construct()
    {
        parent::__construct();
        
        $this->register_filters([]);
        $this->register_closers([]);
    }
    
    protected function rules(): array
    {
        return [
            "page" => [
                "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "auto_correct" => true, 
                "filter" => null, "closer" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
                "method" => UME::GET, "require" => true
            ],
            "test2" => [
                "type" => "alphabet", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "auto_correct" => true, 
                "filter" => null, "closer" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
                "method" => UME::GET, "require" => false
            ],
            "test3" => [
                "type" => "digit", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "auto_correct" => true, 
                "filter" => "string", "closer" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
                "method" => UME::GET, "require" => false
            ],
            "test[]" => [
                "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "auto_correct" => true, 
                "filter" => "string", "closer" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
                "method" => UME::GET, "require" => false
            ],
        ];
    }
    
    protected function labels(): array
    {
        return [
            "ja_JP" => [
                "page"  => "ページ",
                "test"  => "テスト",
                "test3" => "テスト3",
                "test[]"    => "テスト",
                "param" => "データ値"
            ],
        ];
    }
    
    public function conflict_register_types()
    {
        $this->register_types([
            "int" => [
                "type"      => UME::TYPE_INTEGER,
                "correct"   => function($value):string
                {
                    $value  = mb_convert_kana($value, "n", self::ENCODE);       // n:「全角」数字を「半角」に変換します。
                    $value  = STR::normalize_hyphen($value);
                    return $value;
                },
                "rule"      => function($value):bool    { return (bool)preg_match("/\A\-?([1-9]\d*|0)\z/u", $value); },
                "error"     => function($label, $conditions):string { return "[{$label}] は整数でなければなりません。"; }
            ],
        ]);
    }
    
    public function conflict_register_filters()
    {
        $this->register_filters([
            "int"       => function($value) { return (int)$value; },
        ]);
    }
    
    public function conflict_register_closers()
    {
        $this->register_closers([
            "base64"    => function($value) { return base64_encode($value); },
        ]);
    }
    
    public function conflict_register_rules()
    {
        $this->register_rules([
            "page" => [
                "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "auto_correct" => true, 
                "filter" => null, "closer" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
                "method" => UME::GET, "require" => true,
                "something" => "test"
            ],
        ]);
    }
    
    public function conflict_register_labels()
    {
        $this->register_labels([
            "ja_JP" => [
                "page"  => "ページ",
            ],
        ]);
    }
    
}
