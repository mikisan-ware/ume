<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用バリデーター
 * Start Date  : 2021/08/15
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\ume\SINGLE;
use \mikisan\core\exception\UMEException;
//
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

if(!defined("LIBRARY_DEBUG"))   { define("LIBRARY_DEBUG", true); }

class SINGLE_Test extends TestCase
{
    use TestCaseTrait;
    
    protected   $ume;
    
    private     $class_name = "mikisan\\core\\basis\\ume\\FILE";

    public function setUp(): void
    {
        $this->ume              = new ChildUME();
    }
    
    private function get_response(): \stdClass
    {
        $response               = new \stdClass();
        $response->has_error    = false;
        $response->on_error     = false;
        $response->VE           = [];
        $response->offset       = [];
        $response->src          = [];
        $response->dist         = [];
        $response->index        = "";
        return $response;
    }
    
    public function test_validate_regular()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::POST, "require" => true
        ];
        $response   = $this->get_response();
        $src        = "１２３４５";
        //
        $file = SINGLE::validate($this->ume, $src, $key, $conditions, $response);
        $this->assertSame(false, $response->has_error);
    }
    
    public function test_validate_regular_with_validation_error()
    {
        $key        = "test";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::POST, "require" => true
        ];
        $response   = $this->get_response();
        $src        = "ＡＢＣＤＥＦＧ";
        //
        $file = SINGLE::validate($this->ume, $src, $key, $conditions, $response);
        $this->assertSame(true, $response->has_error);
        $this->assertSame("[テスト] は整数でなければなりません。", $response->VE[0]);
    }
    
    public function test_validate_file()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "jpg|png|gif",
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $response   = $this->get_response();
        $src                = [];
        $src["name"]        = "ファイルアップロードテスト.jpg";
        $src["type"]        = "image/jpeg";
        $src["real_type"]   = "image/jpeg";
        $src["tmp_name"]    = __DIR__ . "/folder/upload_test_file.jpg";
        $src["error"]       = 0;
        $src["size"]        = 17498;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        $note       = $conditions["choice"];
        //
        $file = SINGLE::validate($this->ume, $src, $key, $conditions, $response);
        $this->assertSame(false, $response->has_error);
    }
    
    public function test_validate_file_with_validation_error()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "txt",
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $response   = $this->get_response();
        $src                = [];
        $src["name"]        = "ファイルアップロードテスト.jpg";
        $src["type"]        = "image/jpeg";
        $src["real_type"]   = "image/jpeg";
        $src["tmp_name"]    = __DIR__ . "/folder/upload_test_file.jpg";
        $src["error"]       = 0;
        $src["size"]        = 17498;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        $note       = $conditions["choice"];
        //
        $file = SINGLE::validate($this->ume, $src, $key, $conditions, $response);
        $this->assertSame(true, $response->has_error);
        $this->assertSame("[{$label}] のファイルタイプは許可されていません。（許容値：{$note}）", $response->VE[0]);
    }
    
}
