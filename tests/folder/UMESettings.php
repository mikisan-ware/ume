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

use \mikisan\core\basis\ume\UME;
use \mikisan\core\exception\UMEException;
use \mikisan\core\util\str\STR;

class UMESettings
{
    
    const   ENCODE              = "UTF-8";
    const   DEFAULT_MAX_STRING  = 4096;
    const   DEFAULT_METHOD      = UME::POST;
    const   DEFAULT_REQUIRE     = false;
    
    public static function types(): array
    {
        return [
            // digit 全て数字か？
            "digit" => [
                "type"      => UME::TYPE_STRING,
                "correct"   => function($value):string  { return mb_convert_kana($value, "n", self::ENCODE); },      // n:「全角」数字を「半角」に変換します。
                "rule"      => function($value):bool    { return ctype_digit($value); },
                "error"     => function($label, $conditions):string { return "[{$label}] には数字以外が含まれています。"; }
            ],
            // alphabet 全てアルファベットか？
            "alphabet" => [
                "type"      => UME::TYPE_STRING,
                "correct"   => function($value):string  { return mb_convert_kana($value, "r", self::ENCODE); },      // r:「全角」英字を「半角」に変換します。
                "rule"      => function($value):bool    { return ctype_alpha($value); },
                "error"     => function($label, $conditions):string { return "[{$label}] には英字以外が含まれています。"; }
            ],
            // int
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
            "double" => [
                "type"      => UME::TYPE_REAL,
                "correct"   => function($value):string
                {
                    $value  = mb_convert_kana($value, "n", self::ENCODE);       // a:「全角」英数字を「半角」に変換します。（U+0022, U+0027, U+005C, U+007Eを除く U+0021 - U+007E の範囲）
                    $value  = STR::normalize_hyphen($value);
                    return $value;
                },
                "rule"      => function($value):bool    { return (bool)preg_match("/\A\-?([1-9]\d*|0)[.]\d+\z/u", $value); },
                "error"     => function($label, $conditions):string { return "[{$label}] は数値でなければなりません。"; }
            ],
            "text" => [
                "type"      => UME::TYPE_STRING,
                "correct"   => null,
                "rule"      => function($value):bool    { return is_string($value); },
                "error"     => function($label, $conditions):string { return "[{$label}] は文字列でなければなりません。"; }
            ],
            "file" => [
                "type"      => UME::TYPE_FILE,
                "correct"   => null,
                "rule"      => null,
                "error"     => null
            ],
            //
            "wrong_corrector" => [
                "type"      => UME::TYPE_INTEGER,
                "correct"   => ["something", "wrong", "type", "parameter"],
                "rule"      => function($value):bool    { return (bool)preg_match("/\A\-?([1-9]\d*|0)\z/u", $value); },
                "error"     => function($label, $conditions):string { return "[{$label}] は整数でなければなりません。"; }
            ],
            "wrong_rule" => [
                "type"      => UME::TYPE_INTEGER,
                "correct"   => function($value):string
                {
                    $value  = mb_convert_kana($value, "n", self::ENCODE);       // n:「全角」数字を「半角」に変換します。
                    $value  = STR::normalize_hyphen($value);
                    return $value;
                },
                "rule"      => ["something", "wrong", "type", "parameter"],
                "error"     => function($label, $conditions):string { return "[{$label}] は整数でなければなりません。"; }
            ],
            "wrong_return" => [
                "type"      => UME::TYPE_INTEGER,
                "correct"   => function($value):string
                {
                    $value  = mb_convert_kana($value, "n", self::ENCODE);       // n:「全角」数字を「半角」に変換します。
                    $value  = STR::normalize_hyphen($value);
                    return $value;
                },
                "rule"      => function($value) { return (string)preg_match("/\A\-?([1-9]\d*|0)\z/u", $value); },
                "error"     => function($label, $conditions):string { return "[{$label}] は整数でなければなりません。"; }
            ],
            "wrong_error" => [
                "type"      => UME::TYPE_INTEGER,
                "correct"   => function($value):string
                {
                    $value  = mb_convert_kana($value, "n", self::ENCODE);       // n:「全角」数字を「半角」に変換します。
                    $value  = STR::normalize_hyphen($value);
                    return $value;
                },
                "rule"      => function($value):bool    { return false; },
                "error"     => ["something", "wrong", "type", "parameter"]
            ],
        ];
    }
    
    public static function filters(): array
    {
        return [
            "base64"    => function($value) { return base64_decode($value, false); },
            "htmlspec"  => function($value) { return htmlspecialchars_decode($value, ENT_QUOTES|ENT_HTML5); }
        ];
    }
    
    public static function closers(): array
    {
        return [
            "base64"    => function($value) { return base64_encode($value); },
            "htmlspec"  => function($value) { return htmlspecialchars($value, ENT_QUOTES|ENT_HTML5, self::ENCODE); }
        ];
    }
    
}
