<?php
/**
 * Project Name: Pine
 * Description : MIMEタイプユーティリティ
 * Start Date  : 2018/09/03
 * Copyright   : Katsuhiko Miki   http://feijoa.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\util\mime;

use \mikisan\core\util\mime\MIMESettings;

class MIME
{
    /**
     * $pathで指定されたファイルのMIMEタイプを取得する
     * 
     * @param   string      $path
     * @return  string
     * @throws  \pine\PineException
     */
    public static function getMIME(string $path): string
    {
        if (!is_file($path))                { throw new \Exception("指定されたパスが存在しません。[{$path}]"); }
        if (!function_exists("finfo_open")) { throw new \Exception("finfo_open() はPHPで読み込まれていません。"); }

        $data       = file_get_contents($path);
        $finfo      = finfo_open(FILEINFO_MIME_TYPE);   // MIMEタイプ取得に設定する
        $mime_type  = finfo_buffer($finfo, $data);
        finfo_close($finfo);
        
        if(!is_string($mime_type))  { throw new \Exception( "MIME-Typeが識別できませんでした。[{$path}]"); }

        return $mime_type;
    }
    
    /**
     * MIMESettingsクラスを参照して$target_mimeで指定されたMIMEタイプから候補となる拡張子のリストを取得する
     * 
     * @param   string $target_mime
     * @return  array
     */
    public static function getExtentionByMIMEType(string $target_mime): array
    {
        $result = [];
        
        foreach(MIMESettings::getMIMESettings() as $ext => $mimes)
        {
            if(is_string($mimes))   { $mimes = [$mimes]; }
            if(in_array($target_mime, $mimes, true))
            {
                $result[] = $ext;
            }
        }
        return $result;
    }
}
