<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ルーター
 * Start Date  : 2021/07/17
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\ume\UME;
use \mikisan\core\basis\ume\DATASOURCE;
use \mikisan\core\util\router\Router;

$project_root = realpath(__DIR__ . "/../../../../");
require "{$project_root}/vendor/autoload.php";
require_once "{$project_root}/tests/TestCaseTrait.php";
require "{$project_root}/core/basis/ume/src/BaseUME.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/../tests", true);

class DATASOURCE_Test extends TestCase
{
    use TestCaseTrait;

    public function setUp(): void
    {
        $_POST["param1"]    = "abc";
        $_POST["param2"]    = "123";
        $_GET["param1"]     = "def";
        $_GET["param2"]     = "456";
        $_COOKIE["param1"]  = "ghi";
        $_COOKIE["param2"]  = "789";
    }
    
    public function test_get_post()
    {
        $this->assertSame("abc", DATASOURCE::get("post", "param1"));
        $this->assertSame("123", DATASOURCE::get("post", "param2"));
        $this->assertSame(null, DATASOURCE::get("post", "param3"));
    }
    
    public function test_get_get()
    {
        $this->assertSame("def", DATASOURCE::get("get", "param1"));
        $this->assertSame("456", DATASOURCE::get("get", "param2"));
        $this->assertSame(null, DATASOURCE::get("get", "param3"));
    }
    
    public function test_get_cookie()
    {
        $this->assertSame("ghi", DATASOURCE::get("cookie", "param1"));
        $this->assertSame("789", DATASOURCE::get("cookie", "param2"));
        $this->assertSame(null, DATASOURCE::get("cookie", "param3"));
    }
    
    public function test_get_restful()
    {
        $yml_path   = __DIR__ . "/routes.yml";
        $_SERVER["SERVER_NAME"]     = "striking-forces.jp";
        $_SERVER["REQUEST_METHOD"]  = "PUT";
        $_SERVER["REQUEST_URI"]     = "/admin/service/master/357/register/246";
        Router::resolve($yml_path);
        
        $this->assertSame("357", DATASOURCE::get("restful", "id"));
        $this->assertSame("246", DATASOURCE::get("restful", "num"));
        $this->assertSame(null, DATASOURCE::get("restful", "param1"));
    }
    
    public function test_get_args()
    {
        $yml_path   = __DIR__ . "/routes.yml";
        $_SERVER["SERVER_NAME"]     = "striking-forces.jp";
        $_SERVER["REQUEST_METHOD"]  = "FETCH";
        $_SERVER["REQUEST_URI"]     = "/admin/service/show/abcd/efgh/987";
        Router::resolve($yml_path);
        
        $this->assertSame("abcd", DATASOURCE::get("args", 0));
        $this->assertSame("efgh", DATASOURCE::get("args", 1));
        $this->assertSame("987", DATASOURCE::get("args", 2));
        $this->assertSame(null, DATASOURCE::get("args", 3));
    }
    
}
