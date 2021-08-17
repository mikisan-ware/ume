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

use \mikisan\core\basis\ume\UMESettings;
use \mikisan\core\exception\UMEException;
use \mikisan\core\util\ex\EX;
use \mikisan\core\util\mime\MIME;

class CHOICE
{
    
    public static function do(UME $ume, $value, string $key, array $conditions, \stdClass $response): bool
    {
        $labels     = $ume->get_labels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        $allowed    = self::get_allowed($conditions["choice"], $label);
        
        if(in_array($value, $allowed, true))   { return true; }
        
        $note       = implode("|", $allowed);
        $response->VE[$key] = "[{$label}] の値は許可されていません。（許容値：{$note}）";
        
        return false;
    }
    
    public static function filedo(UME $ume, $value, string $key, array $conditions, \stdClass $response): bool
    {
        $labels     = $ume->get_labels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        
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
        
        $note       = implode("|", $allowed);
        $response->VE[$key] = "[{$label}] のファイルタイプは許可されていません。（許容値：{$note}）";
        
        return false;
    }
    
    private static function get_allowed($choice, string $label): array
    {
        $allowed_exts    = null;
        if(is_array($choice))   { $allowed_exts = $choice; }
        if(is_string($choice))  { $allowed_exts = explode("|", $choice); }
        
        if(!is_array($allowed_exts))
        {
            throw new UMEException("[{$label}] の choice の型が不正です。配列で指定してください。");
        }
        
        return $allowed_exts;
    }
    
}
