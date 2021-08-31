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
use \mikisan\core\basis\ume\SELECTOR;
use \mikisan\core\exception\UMEException;
use \mikisan\core\util\ex\EX;

class FILE
{
    
    /**
     * $conditions["method"] => UME::FILES のバリデート
     * 
     * @param   UME         $ume
     * @param   array       $file
     * @param   string      $type
     * @param   string      $key
     * @param   array       $conditions
     * @param   \stdClass   $response
     * @return  array
     */
    public static function validate(UME $ume, array $file, string $key, array $conditions, \stdClass $response): array
    {
        // アップロードファイルのセキュリティチェック
        self::filecheck($ume, $file, $key, $response);
        
        // 拡張子チェック
        $is_in_range    = (isset($conditions["choice"]))
                                ? CHOICE::isInListFileType($ume, $file, $key, $conditions, $response)
                                : self::filetype_undefineded($ume, $file, $key, $response)
                                ;
        return $file;
    }
    
    private static function filetype_undefineded(UME $ume, $value, string $key, \stdClass $response): void
    {
        $label  = SELECTOR::getLabel($ume, $key, $response);
        
        throw new UMEException("[{$label}] でアップロード可能なファイル形式（拡張子）を choice で指定してください。");
    }
    
    private static function filecheck(UME $ume, $value, string $key, \stdClass $response): bool
    {
        $label  = SELECTOR::getLabel($ume, $key, $response);
        
        // 正常にアップロードされたか？
        if($value["error"] !== 0)
        {
            switch(true)
            {
                case $value["error"] === UPLOAD_ERR_NO_FILE:        // ファイル未選択
                    
                    throw new UMEException("[{$label}] はファイルが選択されていません。[code: {$value["error"]}]");
                    
                case $value["error"] === UPLOAD_ERR_INI_SIZE:       // php.ini定義の最大サイズ超過
                case $value["error"] === UPLOAD_ERR_FORM_SIZE:      // フォーム定義の最大サイズ超過 (設定した場合のみ)
                    
                    throw new UMEException("ファイルサイズが大きすぎます。[code: {$value["error"]}]");
                    
                default:
                    
                    throw new UMEException("[{$label}] は正常にアップロード出来ませんでした。[code: {$value["error"]}]");
            }
        }
        if($value["size"] === 0)
        {
            throw new UMEException("[{$label}] は正常にアップロード出来ませんでした。ファイルサイズが 0 です。");
        }
        
        // 正規にアップロードされたファイルか？
        if(!defined("LIBRARY_DEBUG") && !is_uploaded_file($value["tmp_name"]))
        {
            throw new UMEException("[{$label}] は正規のアップロードファイルではありません。");
        }
        
        // Directory Traversal 対策
        if(EX::empty($value["name"]))
        {
            throw new UMEException("アップロードされたファイルのファイル名が空です。[{$label}]");
        }
        if(preg_match("|\A\.|u", $value["name"]) || preg_match("|\.\z|u", $value["name"]) )
        {
            throw new UMEException("先頭または末尾が . の名前のファイルのアップロードは許可されていません。[{$label}]");
        }
        if(preg_match("|/|u", $value["name"]))
        {
            throw new UMEException("ファイル名に / を含むファイルのアップロードは許可されていません。[{$label}]");
        }
        if(preg_match("|\\\\|u", $value["name"]))
        {
            throw new UMEException("ファイル名に \\ を含むファイルのアップロードは許可されていません。[{$label}]");
        }
        
        return true;
    }
    
}
