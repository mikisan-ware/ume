<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/02
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

class UMESettings
{
    
    public  $empty_value    = "";
    
    public static function types(): array
    {
        return [
            // digit 全て数字か？
            "digit" => [
                "type"      => UME::TYPE_STRING,
                "correct"   => function($value){ return mb_convert_kana($req, "n", "UTF-8"); },
                "rule"      => function($value){ return ctype_digit($value); },
                "error"     => function($key, $conditions){ return "[{$conditions["name"]}] には数字以外が含まれています。"; }
            ],
            // alphabet 全てアルファベットか？
            "alphabet" => [
                "type"      => UME::TYPE_STRING,
                "correct"   => function($value){ return mb_convert_kana($value, "r", "UTF-8"); },
                "rule"      => function($value){ return ctype_alpha($value); },
                "error"     => function($key, $conditions){ return "[{$conditions["name"]}] には英字以外が含まれています。"; }
            ],
            // int
            "int" => [
                "type"      => UME::TYPE_INTEGER,
                "correct"   => function($value)
                {
                    $value  = mb_convert_kana($value, "a", "UTF-8");
                    $value  = $obj->adjustHyphen($value);
                    return $value;
                },
                "rule"      => function($value){ return preg_match("/\A\-?([1-9]\d*|0)\z/", $value); },
                "error"     => function($key, $conditions){ return "[{$conditions["name"]}] は整数でなければなりません。"; }
            ]
        ];
    }
    
}
