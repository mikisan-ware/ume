<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/21
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\basis\ume\UME;
use \mikisan\core\util\ex\EX;
use \mikisan\core\util\str\STR;

class VALUE
{
    
    /**
     * $conditions["method"] => UME::FILES 以外のバリデート
     * 
     * @param   UME         $ume
     * @param   mixed       $value
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $response
     * @return  mixed
     */
    public static function validate(UME $ume, $value, string $key, array $conditions, \stdClass $response)
    {
        // エンコード矯正
        if(is_string($value))
        {
            $value  = mb_convert_encoding($value, UMESettings::ENCODE, $ume->get_from_encoding());
        }
        
        // 事前処理：事前フィルタ適用、オートコレクト、NULLバイト除去、トリム
        $value  = self::prepare($ume, $value, $conditions);
        
        // バリデーション処理
        if(REQUIREMENT::should_validate($ume, $value, $key, $conditions, $response))
        {
            // バリデーション処理
            $result = VALIDATOR::do($ume, $value, $key, $conditions, $response);
            
            if($result)
            {
                // 値確定処理：タイプキャスト、事後フィルタ
                $value  = self::conclude($ume, $value, $conditions);

                // 許容範囲チェック
                $is_in_range    = (isset($conditions["choice"]))
                                        ? CHOICE::isInListValue ($ume, $value, $key, $conditions, $response)
                                        : RANGE ::isInRange     ($ume, $value, $key, $conditions, $response)
                                        ;
            }
        }
        
        return $value;
    }
    
    /**
     * 事前処理
     * 
     * @param   UME     $ume
     * @param   mixed   $value
     * @param   array   $conditions
     * @return  mixed
     */
    private static function prepare(UME $ume, $value, array $conditions)
    {
        // 事前フィルタ
        if(!EX::empty($conditions["filter"]))       { $value = FILTER::do($ume->get_filters(), $value, $conditions); }
        
        // オートコレクト
        if($conditions["auto_correct"] === true)    { $value = CORRECTOR::do($ume->get_types()[$conditions["type"]], $value, $conditions); }
        
        if(is_string($value))
        { 
            // NULLバイト除去
            if($conditions["null_byte"] === false)  { $value = STR::strip_null($value); }
        
            // トリム
            $value = TRIM::do($value, $conditions);
        }
    
        return $value;
    }
    
    /**
     * 値確定処理
     * 
     * @param   UME     $ume
     * @param   mixed   $value
     * @param   string  $type
     * @param   array   $conditions
     * @return  mixed
     */
    private static function conclude(UME $ume, $value, array $conditions)
    {
        if(is_null($value)) { return null; }

        // タイプキャスト
        $value  = TYPECAST::do($ume->get_types()[$conditions["type"]]["type"], $value);

        // 事後フィルタ
        $value  = CLOSER::do($ume->get_closers(), $value, $conditions);
        
        return $value;
    }
    
}
