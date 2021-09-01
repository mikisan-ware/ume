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

use \mikisan\core\basis\ume\SELECTOR;
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
     * @param   \stdClass   $result
     * @return  bool        バリデーションを行うか？のフラグ
     */
    public static function should_validate(UME $ume, $value, string $key, array $conditions, \stdClass $result): bool
    {
        if  (!EX::empty($value))    { return true; }
        
        return (bool)((int)$conditions["require"] & 0b0001)
                    ? self::fail($ume, $key, $result)
                    : false
                    ;
    }
    
    /**
     * 必須設定での分岐
     * 
     * @param   UME         $ume
     * @param   mixed       $value
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $result
     * @return  bool        バリデーションを行うか？のフラグ
     */
    public static function should_force_validate(UME $ume, $value, string $key, array $conditions, \stdClass $result): bool
    {
        if  (!EX::empty($value))    { return true; }
        
        return (bool)((int)$conditions["require"] & 0b0010)
                    ? self::fail($ume, $key, $result)
                    : false
                    ;
    }
    
    /**
     * 必須項目が入力されなかった
     * 
     * @param UME $ume
     * @param string $value
     * @param string $key
     * @param \stdClass $result
     * @return bool
     */
    public static function fail(UME $ume, string $key, \stdClass $result): bool
    {
        $label  = SELECTOR::getLabel($ume, $key, $result);
        $result->VE[]       = "[$label] は必須項目です。";
        $result->on_error   = true;

        return false;
    }
    
}
