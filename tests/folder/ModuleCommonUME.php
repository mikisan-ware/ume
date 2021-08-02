<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/02
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\pine\app;

use \mikisan\core\basis\dto\Dto;

abstract class ModuleCommonUME extends SiteCommonUME
{
    
    public function __construct(Dto $dto)
    {
        parent::__construct($dto);
        
        $this->module_common_types();
    }
    
    private function module_common_types(): void
    {
        $this->register_types([]);
    }
    
}
