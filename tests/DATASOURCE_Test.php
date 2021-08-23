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
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class DATASOURCE_Test extends TestCase
{
    use TestCaseTrait;
    
    protected $ume;

    public function setUp(): void
    {
        $this->ume  = new ChildUME();
        
        $_POST["param1"]    = "abc";
        $_POST["param2"]    = "123";
        $_GET["param1"]     = "def";
        $_GET["param2"]     = "456";
        $_COOKIE["param1"]  = "ghi";
        $_COOKIE["param2"]  = "789";
    }
    
    public function test_get_post()
    {
        $this->assertSame("abc", DATASOURCE::get($this->ume, UME::POST, "param1"));
        $this->assertSame("123", DATASOURCE::get($this->ume, UME::POST, "param2"));
        $this->assertSame(null, DATASOURCE::get($this->ume, UME::POST, "param3"));
    }
    
    public function test_get_get()
    {
        $this->assertSame("def", DATASOURCE::get($this->ume, UME::GET, "param1"));
        $this->assertSame("456", DATASOURCE::get($this->ume, UME::GET, "param2"));
        $this->assertSame(null, DATASOURCE::get($this->ume, UME::GET, "param3"));
    }
    
    public function test_get_cookie()
    {
        $this->assertSame("ghi", DATASOURCE::get($this->ume, UME::COOKIE, "param1"));
        $this->assertSame("789", DATASOURCE::get($this->ume, UME::COOKIE, "param2"));
        $this->assertSame(null, DATASOURCE::get($this->ume, UME::COOKIE, "param3"));
    }
    
    public function test_get_restful()
    {
        $yml_path   = __DIR__ . "/folder/routes.yml";
        $_SERVER["SERVER_NAME"]     = "striking-forces.jp";
        $_SERVER["REQUEST_METHOD"]  = "PUT";
        $_SERVER["REQUEST_URI"]     = "/admin/service/master/357/register/246";
        Router::resolve($yml_path);
        
        $this->assertSame("357", DATASOURCE::get($this->ume, UME::RESTful, "id"));
        $this->assertSame("246", DATASOURCE::get($this->ume, UME::RESTful, "num"));
        $this->assertSame(null, DATASOURCE::get($this->ume, UME::RESTful, "param1"));
    }
    
    public function test_get_args()
    {
        $yml_path   = __DIR__ . "/folder/routes.yml";
        $_SERVER["SERVER_NAME"]     = "striking-forces.jp";
        $_SERVER["REQUEST_METHOD"]  = "FETCH";
        $_SERVER["REQUEST_URI"]     = "/admin/service/show/abcd/efgh/987";
        Router::resolve($yml_path);
        
        $this->assertSame("abcd", DATASOURCE::get($this->ume, UME::ARGS, 0));
        $this->assertSame("efgh", DATASOURCE::get($this->ume, UME::ARGS, 1));
        $this->assertSame("987", DATASOURCE::get($this->ume, UME::ARGS, 2));
        $this->assertSame(null, DATASOURCE::get($this->ume, UME::ARGS, 3));
    }
    
}
