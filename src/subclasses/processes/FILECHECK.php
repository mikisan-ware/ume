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

class FILECHECK
{
    
    public static function do(UME $ume, $value, string $key): bool
    {
        $labels     = $ume->get_labels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        
        // 正常にアップロードされたか？
        if($value["error"] !== 0)
        {
            throw new UMEException("[{$label}] は正常にアップロード出来ませんでした。[code: {$value["error"]}]");
        }
        if($value["size"] === 0)
        {
            throw new UMEException("[{$label}] は正常にアップロード出来ませんでした。[size: 0]");
        }
        
        // 正規にアップロードされたファイルか？
        if(!defined("DEBUG") && !is_uploaded_file($value["tmp_name"]))
        {
            throw new UMEException("[{$label}] は正規のアップロードファイルではありません。");
        }
        
        // Directory Traversal 対策
        if(preg_match("|(\A\.|\.\z)|u", $value["name"]))
        {
            throw new UMEException("先頭または末尾が . の名前のファイルのアップロードは許可されていません。[{$label}]");
        }
        if(preg_match("|\A\.|u", $value["name"]))
        {
            throw new UMEException("/ を含む名前のファイルのアップロードは許可されていません。[{$label}]");
        }
        
        return true;
    }
    
}
