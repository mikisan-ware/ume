<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/07/17
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\ume\REQUIREMENT;
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class REQUIREMENT_Test extends TestCase
{
    use TestCaseTrait;
    
    protected   $ume;
    
    private     $class_name = "mikisan\\core\\basis\\ume\\REQUIREMENT";

    public function setUp(): void
    {
        $this->ume      = new ChildUME();
    }
    
    private function get_response(): \stdClass
    {
        $response               = new \stdClass();
        $response->VE           = [];
        $response->has_error    = false;
        $response->index        = "[要素: ".rand(1,100)."]";
        
        return $response;
    }
    
    /**
     * 必須項目が入力されなかった時の処理テスト
     */
    public function test_fail()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $string     = "あいうえお漢字<script>alert(\"XSS!\");ABC12345=!*";
        $value      = "";
        $response   = $this->get_response();
        $labels     = $this->ume->get_labels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $this->assertSame(false, $this->callMethod($this->class_name, "fail", [$this->ume, $key, $response]));
        $this->assertSame("[$label] は必須項目です。{$response->index}", $response->VE[$key]);
        $this->assertSame(true, $response->has_error);
    }
    
    /**
     * 必須項目が入力された時の処理テスト
     */
    public function test_required_has_value()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $value      = "あいうえお漢字<script>alert(\"XSS!\");ABC12345=!*";
        $response   = $this->get_response();
        //
        $result     = REQUIREMENT::should_validate($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
        $this->assertSame(false, $response->has_error);
    }
    
    /**
     * 必須項目が入力されなかった時の処理テスト
     */
    public function test_required_is_empty()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $value      = "";
        $response   = $this->get_response();
        $labels     = $this->ume->get_labels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = REQUIREMENT::should_validate($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame(true, $response->has_error);
        $this->assertSame("[$label] は必須項目です。{$response->index}", $response->VE[$key]);
    }
    
    /**
     * 必須項目がnullの時の処理テスト
     */
    public function test_required_is_null()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => true
        ];
        $value      = null;
        $response   = $this->get_response();
        $labels     = $this->ume->get_labels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = REQUIREMENT::should_validate($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame(true, $response->has_error);
        $this->assertSame("[$label] は必須項目です。{$response->index}", $response->VE[$key]);
    }
    
    /**
     * 非必須項目が入力された時の処理テスト
     */
    public function test_unrequired_has_value()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value      = "あいうえお漢字<script>alert(\"XSS!\");ABC12345=!*";
        $response   = $this->get_response();
        //
        $result     = REQUIREMENT::should_validate($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
        $this->assertSame(false, $response->has_error);
    }
    
    /**
     * 非必須項目が入力された時の処理テスト
     */
    public function test_unrequired_is_empty()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value      = "";
        $response   = $this->get_response();
        //
        $result     = REQUIREMENT::should_validate($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame(false, $response->has_error);
    }
    
    /**
     * 非必須項目が入力された時の処理テスト
     */
    public function test_unrequired_is_null()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value      = null;
        $response   = $this->get_response();
        //
        $result     = REQUIREMENT::should_validate($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame(false, $response->has_error);
    }
    
}
