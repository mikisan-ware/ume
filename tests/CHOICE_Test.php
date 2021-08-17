<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/17
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\ume\CHOICE;
use \mikisan\core\exception\UMEException;
//
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

$project_root = realpath(__DIR__ . "/../../../../");
require "{$project_root}/vendor/autoload.php";
require_once "{$project_root}/tests/TestCaseTrait.php";
Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class CHOICE_Test extends TestCase
{
    use TestCaseTrait;
    
    protected $ume;

    public function setUp(): void
    {
        $this->ume      = new ChildUME();
    }
    
    /**
     * choiceが | 区切りのstringの場合の通常パラメータのテスト
     */
    public function test_do_string()
    {
        $response       = new \stdClass();
        $response->VE   = [];
        $response->src  = [];
        $key            = "test";
        $conditions     = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "abc|def|ghi",
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value          = "def";
        
        $result     = CHOICE::do($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
    }
    
    /**
     * choiceがarrayの場合の通常パラメータのテスト
     */
    public function test_do_array()
    {
        $response       = new \stdClass();
        $response->VE   = [];
        $response->src  = [];
        $key            = "test";
        $conditions     = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => ["abc", "def", "ghi"],
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value          = "def";
        
        $result     = CHOICE::do($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
    }
    
    /**
     * choiceが | 区切りのstringの場合の通常パラメータ（非許容値）のテスト
     */
    public function test_do_ummatch()
    {
        $response       = new \stdClass();
        $response->VE   = [];
        $response->src  = [];
        $key            = "test";
        $conditions     = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "abc|def|ghi",
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value          = "jkl";
        $labels         = $this->ume->get_labels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        $note           = "abc|def|ghi";
        
        $result     = CHOICE::do($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] の値は許可されていません。（許容値：{$note}）", $response->VE[$key]);
    }
    
    /**
     * choiceが数値arrayの場合の通常パラメータのテスト
     */
    public function test_do_int_array()
    {
        $response       = new \stdClass();
        $response->VE   = [];
        $response->src  = [];
        $key            = "test";
        $conditions     = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => [1, 2, 3],
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value          = 3;
        
        $result     = CHOICE::do($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
    }
    
    /**
     * choice指定が配列以外のテスト
     */
    public function test_do_exception()
    {
        $response       = new \stdClass();
        $response->VE   = [];
        $response->src  = [];
        $key            = "test";
        $conditions     = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => 123,
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value          = "def";
        $labels     = $this->ume->get_labels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("[{$label}] の choice の型が不正です。配列で指定してください。");
        
        $result     = CHOICE::do($this->ume, $value, $key, $conditions, $response);
    }
    
    /**
     * choiceが | 区切りのstringの場合のファイルのテスト
     */
    public function test_extdo_string()
    {
        $response       = new \stdClass();
        $response->VE   = [];
        $response->src  = [];
        $key            = "test";
        $conditions     = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "jpg|png|gif",
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => false
        ];
        $value = array();
        $value["name"]          = "ファイルアップロードテスト.png";
        $value["type"]          = "image/png";
        $value["real_type"]     = "image/png";
        $value["tmp_name"]      = "/tmp/phpn3FmFr";
        $value["error"]         = 0;
        $value["size"]          = 15476;
        
        $result     = CHOICE::filedo($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
    }
    
    /**
     * choiceがarrayの場合のファイルのテスト
     */
    public function test_extdo_array()
    {
        $response       = new \stdClass();
        $response->VE   = [];
        $response->src  = [];
        $key            = "test";
        $conditions     = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => ["jpg", "png", "gif"],
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::GET, "require" => false
        ];
        $value = array();
        $value["name"]          = "ファイルアップロードテスト.png";
        $value["type"]          = "image/png";
        $value["real_type"]     = "image/png";
        $value["tmp_name"]      = "/tmp/phpn3FmFr";
        $value["error"]         = 0;
        $value["size"]          = 15476;
        
        $result     = CHOICE::filedo($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(true, $result);
    }
    
    /**
     * choiceが | 区切りのstringの場合のファイル（非許容ファイルタイプ）のテスト
     */
    public function test_extdo_unmatch()
    {
        $response       = new \stdClass();
        $response->VE   = [];
        $response->src  = [];
        $key            = "test";
        $conditions     = [
            "type" => "text", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "jpg|png|gif",
            "auto_correct" => true, "filter" => "base64", "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => false
        ];
        $value = array();
        $value["name"]          = "ファイルアップロードテスト.txt";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = "/tmp/phpn3FmFr";
        $value["error"]         = 0;
        $value["size"]          = 15476;
        $labels         = $this->ume->get_labels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        $note           = "jpg|png|gif";
        
        $result     = CHOICE::filedo($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $result);
        $this->assertSame("[{$label}] のファイルタイプは許可されていません。（許容値：{$note}）", $response->VE[$key]);
    }
    
}
