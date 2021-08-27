<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/16
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\exception\UMEException;
use \mikisan\core\util\ex\EX;

class REQUIREMENT
{
 
    /**
     * 必須設定での分岐
     * 
     * @param   UME         $ume
     * @param   mixed       $value
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $response
     * @return  bool        バリデーションを行うか？のフラグ
     */
    public static function should_validate(UME $ume, $value, string $key, array $conditions, \stdClass $response): bool
    {
        return  (!EX::empty($value))
                    ? true
                    : self::case_empty_value($ume, $value, $key, $conditions, $response)
                    ;
    }
    
    /**
     * 必須入力項目か？ 必須項目が入力されたか？
     * 
     * @return  bool        バリデーションを行うか？のフラグ
     */
    private static function case_empty_value(UME $ume, $value, string $key, array $conditions, \stdClass $response): bool
    {
        return ($conditions["require"] !== false)
                    ? self::fail($ume, $key, $response)
                    : false
                    ;
    }
    
    /**
     * 必須項目が入力されなかった
     * 
     * @param UME $ume
     * @param string $value
     * @param string $key
     * @param \stdClass $response
     * @return bool
     */
    public static function fail(UME $ume, string $key, \stdClass $response): bool
    {
        $labels = $ume->getLabels();
        $label  = $labels["ja_JP"][$key] ?? $key;
        $response->VE[$key]     = "[$label] は必須項目です。";
        $response->has_error    = true;
        $response->on_error     = true;

        return false;
    }
    
}
