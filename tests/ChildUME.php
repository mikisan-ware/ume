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

use \mikisan\core\basis\dto\Dto;

class ChildUME extends ModuleCommonUME
{
    
    public function rules(): array
    {
        return [
            "page" => [
                "name" => "ページ", "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
                "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
                "method" => UME::GET, "require" => false
            ],
        ];
    }
    
}
