<?php

/**
 * Project Name: mikisan-ware
 * Description : バリデーター
 * Start Date  : 2021/07/27
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

use \mikisan\core\basis\ume\DATASOURCE;

class NORMAL
{
    
    public static function parse(Dto $dto, string $key, array $conditions) : void
    {
        //リクエスト取得
        $value  = DATASOURCE::get($dto, $key, $conditions);
        
        // バリデーションを行わない指定の場合は結果に値を設定してreturn
        if($mode !== true)
        {
            $this->requestDatas[$key] = $value;
            return;
        }

        // バリデーション
        $value = $this->doValidate($key, $value, $conditions);
        
        $this->requestDatas[$key] = $value;
        
        if($conditions["method"] === UME::FILES)
        {
            $dto->setPineUploads($key, $value);
        }
    }
    
}
