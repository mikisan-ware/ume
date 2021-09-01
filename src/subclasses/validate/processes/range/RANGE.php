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

use \mikisan\core\basis\ume\UME;
use \mikisan\core\basis\ume\UMESettings;
use \mikisan\core\basis\ume\SELECTOR;
use \mikisan\core\exception\UMEException;
use \mikisan\core\util\ex\EX;

class RANGE
{
    
    /**
     * 入力値が許容値内か？
     * 
     * @param   UME         $ume
     * @param   mixed       $value
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $resobj
     * @return  bool        入力値が許容値内か？のフラグ
     */
    public static function isInRange(UME $ume, $value, string $key, array $conditions, \stdClass $resobj): bool
    {
        $types  = $ume->getTypes();
        $type   = $types[$conditions["type"]]["type"];
        $label  = SELECTOR::getLabel($ume, $key, $resobj);
        
        switch(true)
        {
            case $type === UME::TYPE_INTEGER:   return self::type_integer($value, $key, $label, $conditions, $resobj);
            case $type === UME::TYPE_REAL:      return self::type_real($value, $key, $label, $conditions, $resobj);
            case $type === UME::TYPE_FILE:      return self::type_file($value, $key, $label, $conditions, $resobj);
                
            case $type === UME::TYPE_STRING:    
            default:
                return self::type_string($value, $key, $label, $conditions, $resobj);
        }
    }
    
    private static function type_integer(int $value, string $key, string $label, array $conditions, \stdClass $resobj): bool
    {
        $min    = (int)$conditions["min"] ?? 0;
        $max    = (int)$conditions["max"] ?? PHP_INT_MAX;
        
        if($min <= $value && $value <= $max)    { return true; }
        
        if($value < $min)
        {
            $resobj->VE[]   = "[{$label}] は {$min} 以上の整数にしてください。";
        }
        if($value > $max)
        {
            $resobj->VE[]   = "[{$label}] は {$max} 以下の整数にしてください。";
        }
        $resobj->on_error   = true;
        
        return false;
    }
    
    private static function type_real(float $value, string $key, string $label, array $conditions, \stdClass $resobj): bool
    {
        $min    = (double)$conditions["min"] ?? 0;
        $max    = (double)$conditions["max"] ?? PHP_INT_MAX;
        
        if($min <= $value && $value <= $max)    { return true; }
        
        if($value < $min)
        {
            $resobj->VE[]   = "[{$label}] は {$min} 以上の実数にしてください。";
        }
        if($value > $max)
        {
            $resobj->VE[]   = "[{$label}] は {$max} 以下の実数にしてください。";
        }
        $resobj->on_error   = true;
        
        return false;
    }
    
    private static function type_string(string $value, string $key, string $label, array $conditions, \stdClass $resobj): bool
    {
        $min    = (int)$conditions["min"] ?? 0;
        $max    = (int)$conditions["max"] ?? UMESettings::DEFAULT_MAX_STRING;
        $len    = mb_strlen($value, UMESettings::ENCODE);
        
        if($min <= $len && $len <= $max)    { return true; }
        
        if($len < $min)
        {
            $resobj->VE[]   = "[{$label}] は {$min} 文字以上にしてください。";
        }
        if($len > $max)
        {
            $resobj->VE[]   = "[{$label}] は {$max} 文字以下にしてください。";
        }
        $resobj->on_error   = true;
        
        return false;
    }
    
    private static function type_file(array $value, string $key, string $label, array $conditions, \stdClass $resobj): bool
    {
        $min        = $conditions["min"];
        $max        = $conditions["max"];
        $filesize   = $value["size"];
        
        $allowed_min_size       = is_int($min) ? $min : self::get_actual_size($min, $label) ;
        $allowed_max_size       = is_int($max) ? $max : self::get_actual_size($max, $label) ;
        
        if($filesize < $allowed_min_size)
        {
            $min_limit          = is_int($min) ? "{$min} Bytes" : $min;
            $resobj->VE[]       = "[{$label}] のファイルサイズが小さ過ぎます。許容されているファイルサイズは {$min_limit} です。";
            $resobj->on_error   = true;
            return false;
        }
        if($filesize > $allowed_max_size)
        {
            $max_limit          = is_int($max) ? "{$max} Bytes" : $max;
            $resobj->VE[]       = "[{$label}] のファイルサイズが大き過ぎます。許容されているファイルサイズは {$max_limit} です。";
            $resobj->on_error   = true;
            return false;
        }
        
        return true;
    }

    private static function get_actual_size(string $limit, string $label) : int
    {
        $rule           = strtoupper($limit);
        $size           = (int)$rule;
        $unit           = preg_replace("/\d+/", "", $rule);
        
        switch($unit){
            case "B":       return $size;
            case "KB":      return $size * pow(10, 3);
            case "KIB":     return $size * pow(2, 10);
            case "MB":      return $size * pow(10, 6);
            case "MIB":     return $size * pow(2, 20);
            case "GB":      return $size * pow(10, 9);
            case "GIB":     return $size * pow(2, 30);
            case "TB":      return $size * pow(10, 12);
            case "TIB":     return $size * pow(2, 40);
            case "PB":      return $size * pow(10, 15);
            case "PIB":     return $size * pow(2, 50);
            case "EB":      return $size * pow(10, 18);
            case "EIB":     return $size * pow(2, 60);
        }
        
        throw new UMEException("[{$label}] で指定されたファイルサイズの単位が不正です。[{$unit}]");
    }
    
}
