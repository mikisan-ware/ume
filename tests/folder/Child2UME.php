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

class Child2UME extends ModuleCommonUME
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
                "method" => UME::GET, "require" => true,
                "something" => "test"
            ],
        ];
    }
    
    protected function labels(): array
    {
        return [
            "ja_JP" => [
                "page"  => "ページ",
                "test"  => "テスト",
                "test3" => "テスト3"
            ],
        ];
    }
    
}
