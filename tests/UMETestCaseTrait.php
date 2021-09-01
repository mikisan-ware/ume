<?php

/**
 * Project Name: mikisan-ware
 * Description : private, protectedメソッドテスト用trait
 * Start Date  : 2021/07/18
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

trait UMETestCaseTrait
{

    function get_response(): \stdClass
    {
        $response               = new \stdClass();
        $response->has_error    = false;
        $response->VE           = [];
        $response->offset       = [];
        $response->src          = [];
        $response->dist         = [];
        
        return $response;
    }
    
    function get_resobj(): \stdClass
    {
        $resobj             = new \stdClass();
        $resobj->has_error  = false;
        $resobj->on_error   = false;
        $resobj->VE         = [];
        $resobj->offset     = [];
        $resobj->index      = "";
        
        return $resobj;
    }
    
}
