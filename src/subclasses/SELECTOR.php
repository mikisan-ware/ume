<?php

/**
 * Project Name: mikisan-ware
 * Description : バリデーター
 * Start Date  : 2021/08/31
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

class SELECTOR
{
    
    public static function getLabel(UME $ume, string $key, \stdClass $response): string
    {
        $labels = $ume->getLabels();
        $label  = $labels["ja_JP"][$key] ?? $key;
        
        return $label.$response->index;
    }
    
}
