<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/17
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
use \mikisan\core\util\mime\MIME;

class CHOICE
{
    
    /**
     * 入力値が choice で許可された値か？
     * 
     * @param   UME         $ume
     * @param   mixed       $value
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $resobj
     * @return  bool        入力値が choice で許可された値か？のフラグ
     */
    public static function isInListValue(UME $ume, $value, string $key, array $conditions, \stdClass $resobj): bool
    {
        $label      = SELECTOR::getLabel($ume, $key, $resobj);
        $allowed    = self::get_allowed($conditions["choice"], $label);
        
        if(in_array($value, $allowed, true))   { return true; }
        
        $note               = implode("|", $allowed);
        $resobj->VE[]       = "[{$label}] の値は許可されていません。（許容値：{$note}）";
        $resobj->on_error   = true;
        
        return false;
    }
    
    /**
     * アップロードされたファイルが choice で許可されたファイルタイプか？
     * 
     * @param   UME         $ume
     * @param   mixed       $value
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $resobj
     * @return  bool
     * @throws  UMEException
     */
    public static function isInListFileType(UME $ume, $value, string $key, array $conditions, \stdClass $resobj): bool
    {
        $label      = SELECTOR::getLabel($ume, $key, $resobj);
        
        if(!isset($conditions["choice"]))
        {
            throw new UMEException("[{$label}] で許容するファイルタイプが未設定です。");
        }
        
        $allowed    = self::get_allowed($conditions["choice"], $label);
        $exts       = MIME::getExtentionByMIMEType($value["real_type"]);        // ファイルのMIME-Typeに登録されている拡張子リスト（例：[jpg、jpeg]）
        foreach($exts as $ext)
        {
            if(in_array($ext, $allowed, true))   { return true; }
        }
        
        $note               = implode("|", $allowed);
        $resobj->VE[]       = "[{$label}] のファイルタイプは許可されていません。（許容値：{$note}）";
        $resobj->on_error   = true;
        
        return false;
    }
    
    private static function get_allowed($choice, string $label): array
    {
        $allowed    = null;
        if(is_array($choice))   { $allowed = $choice; }
        if(is_string($choice))  { $allowed = explode("|", $choice); }
        
        if(!is_array($allowed))
        {
            throw new UMEException("[{$label}] の choice の型が不正です。配列で指定してください。");
        }
        
        return $allowed;
    }
    
}
