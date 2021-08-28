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
use \mikisan\core\exception\UMEException;
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
        $_FILES["param1"]   = [
            "name"      => "MyFile.txt",
            "type"      => "text/plain",
            "tmp_name"  => "C:/NetBeansProjects/mikisan/core/basis/ume/tests/folder/upload_test_file.txt",
            "error"     => UPLOAD_ERR_OK,
            "size"      => 1978
        ];
        $_FILES["param2"]   = [
            "name" => [
                "MyFile.txt",
                "MyFile.jpg"
            ],
            "type" => [
                "text/plain",
                "image/jpeg"
            ],
            "tmp_name" => [
                "C:/NetBeansProjects/mikisan/core/basis/ume/tests/folder/upload_test_file.jpg",
                "C:/NetBeansProjects/mikisan/core/basis/ume/tests/folder/upload_test_file2.PNG"
            ],
            "error" => [
                UPLOAD_ERR_OK,
                UPLOAD_ERR_OK
            ],
            "size" => [
                123,
                98174
            ]
        ];
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
    
    public function test_get_files_single()
    {
        $files  = DATASOURCE::get($this->ume, UME::FILES, "param1");
        //
        $this->assertSame("MyFile.txt", $files["name"]);
        $this->assertSame("text/plain", $files["type"]);
        $this->assertSame("C:/NetBeansProjects/mikisan/core/basis/ume/tests/folder/upload_test_file.txt", $files["tmp_name"]);
        $this->assertSame(UPLOAD_ERR_OK, $files["error"]);
        $this->assertSame(1978, $files["size"]);
    }
    
    public function test_get_files_multiple()
    {
        $files  = DATASOURCE::get($this->ume, UME::FILES, "param2");
        $this->assertCount(2, $files);
        //
        $this->assertSame("MyFile.txt", $files[0]["name"]);
        $this->assertSame("image/jpeg", $files[0]["type"]);
        $this->assertSame("C:/NetBeansProjects/mikisan/core/basis/ume/tests/folder/upload_test_file.jpg", $files[0]["tmp_name"]);
        $this->assertSame(UPLOAD_ERR_OK, $files[0]["error"]);
        $this->assertSame(123, $files[0]["size"]);
        //
        $this->assertSame("MyFile.jpg", $files[1]["name"]);
        $this->assertSame("image/png", $files[1]["type"]);
        $this->assertSame("C:/NetBeansProjects/mikisan/core/basis/ume/tests/folder/upload_test_file2.PNG", $files[1]["tmp_name"]);
        $this->assertSame(UPLOAD_ERR_OK, $files[1]["error"]);
        $this->assertSame(98174, $files[1]["size"]);
    }
    
    public function test_get_undefined_method()
    {
        $key            = "params1";
        $target_method  = "undefined";
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("バリデーション設定内に記述された、キー [{$key}] の method [{$target_method}] は定義されていません。");
        DATASOURCE::get($this->ume, $target_method, $key);
    }
    
}
