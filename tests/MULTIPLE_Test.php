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
use \mikisan\core\basis\ume\MULTIPLE;
use \mikisan\core\exception\UMEException;
//
use \mikisan\core\basis\ume\UME;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";
require_once __DIR__ . "/UMETestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

if(!defined("LIBRARY_DEBUG"))   { define("LIBRARY_DEBUG", true); }

class MULTIPLE_Test extends TestCase
{
    use TestCaseTrait;
    use UMETestCaseTrait;
    
    protected   $ume;
    
    private     $class_name = "mikisan\\core\\basis\\ume\\FILE";

    public function setUp(): void
    {
        $this->ume              = new ChildUME();
    }
    
    public function test_validate_not_array_passed()
    {
        $key        = "test[]";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::POST, "require" => true
        ];
        $resobj   = $this->get_resobj();
        $src        = "１２３４５";
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("バリデーションルール {$key} として渡された値が配列ではありません。[type: string]");
        //
        $file = MULTIPLE::validate($this->ume, $src, $key, $conditions, $resobj);
    }
    
    
    public function test_validate_regular()
    {
        $key        = "test[]";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::POST, "require" => true
        ];
        $resobj   = $this->get_resobj();
        $src        = ["１２３４５", "4567", "８９０123"];
        //
        $file = MULTIPLE::validate($this->ume, $src, $key, $conditions, $resobj);
        $this->assertSame(false, $resobj->on_error);
    }
    
    public function test_validate_regular_with_validation_error()
    {
        $key        = "test[]";
        $conditions = [
            "type" => "int", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::POST, "require" => true
        ];
        $resobj   = $this->get_resobj();
        $src        = ["１２３４５", "4567", "ＡＢＣＤＥＦＧ"];
        //
        $file = MULTIPLE::validate($this->ume, $src, $key, $conditions, $resobj);
        $this->assertSame(true, $resobj->on_error);
        $this->assertSame("[テスト:2] は整数でなければなりません。", $resobj->VE[0]);
        $this->assertCount(1, $resobj->offset);
        $this->assertContains(2, $resobj->offset);
    }
    
    public function test_validate_file()
    {
        $key        = "test[]";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "jpg|png|gif",
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $resobj   = $this->get_resobj();
        $data               = [];
        //
        $file               = [];
        $file["name"]       = "ファイルアップロードテスト.jpg";
        $file["type"]       = "image/jpeg";
        $file["real_type"]  = "image/jpeg";
        $file["tmp_name"]   = __DIR__ . "/folder/upload_test_file.jpg";
        $file["error"]      = 0;
        $file["size"]       = 17498;
        $data[0]            = $file;
        //
        $file               = [];
        $file["name"]       = "ファイルアップロードテスト２.jpg";
        $file["type"]       = "image/png";
        $file["real_type"]  = "image/png";
        $file["tmp_name"]   = __DIR__ . "/folder/upload_test_file2.PNG";
        $file["error"]      = 0;
        $file["size"]       = 382458;
        $data[1]            = $file;
        //
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        $note       = $conditions["choice"];
        //
        $file = MULTIPLE::validate($this->ume, $data, $key, $conditions, $resobj);
        $this->assertSame(false, $resobj->on_error);
        $this->assertCount(0, $resobj->offset);
    }
    
    public function test_validate_file_with_validation_error()
    {
        $key        = "test[]";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "txt",
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $resobj   = $this->get_resobj();
        $data               = [];
        //
        $file               = [];
        $file["name"]       = "ファイルアップロードテスト.jpg";
        $file["type"]       = "image/jpeg";
        $file["real_type"]  = "image/jpeg";
        $file["tmp_name"]   = __DIR__ . "/folder/upload_test_file.jpg";
        $file["error"]      = 0;
        $file["size"]       = 17498;
        $data[0]            = $file;
        //
        $file               = [];
        $file["name"]       = "ファイルアップロードテスト２.jpg";
        $file["type"]       = "image/png";
        $file["real_type"]  = "image/png";
        $file["tmp_name"]   = __DIR__ . "/folder/upload_test_file2.PNG";
        $file["error"]      = 0;
        $file["size"]       = 382458;
        $data[1]            = $file;
        //
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        $note       = $conditions["choice"];
        //
        $file = MULTIPLE::validate($this->ume, $data, $key, $conditions, $resobj);
        $this->assertSame(true, $resobj->on_error);     
        $this->assertSame("[{$label}:0] のファイルタイプは許可されていません。（許容値：{$note}）", $resobj->VE[0]);
        $this->assertSame("[{$label}:1] のファイルタイプは許可されていません。（許容値：{$note}）", $resobj->VE[1]);
        $this->assertCount(2, $resobj->offset);
        $this->assertContains(0, $resobj->offset);
        $this->assertContains(1, $resobj->offset);
    }
    
}
