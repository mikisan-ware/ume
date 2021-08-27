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
use \mikisan\core\basis\ume\FILE;
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

class FILE_Test extends TestCase
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
        $response->VE           = [];
        $response->src          = [];
        $response->dist         = [];
        return $response;
    }
    
    public function test_validate()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "txt",
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "ファイルアップロードテスト.txt";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["error"]         = 0;
        $value["size"]          = 1978;
        //
        $file = FILE::validate($this->ume, $value, $key, $conditions, $response);
        $this->assertSame(false, $response->has_error);
    }
    
    public function test_validate_unallowed()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, "choice" => "txt",
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "ファイルアップロードテスト.jpg";
        $value["type"]          = "image/jpeg";
        $value["real_type"]     = "image/jpeg";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.jpg";
        $value["error"]         = 0;
        $value["size"]          = 17498;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        $note           = $conditions["choice"];
        //
        $file = FILE::validate($this->ume, $value, $key, $conditions, $response);
        $this->assertSame("[{$label}] のファイルタイプは許可されていません。（許容値：{$note}）", $response->VE[$key]);
        $this->assertSame(true, $response->has_error);
    }
    
    public function test_validate_choice_undefined()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX,
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "ファイルアップロードテスト.txt";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["error"]         = 0;
        $value["size"]          = 1978;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("[{$label}] でアップロード可能なファイル形式（拡張子）を choice で指定してください。");
        $file = FILE::validate($this->ume, $value, $key, $conditions, $response);
    }
    
    public function test_filecheck()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "ファイルアップロードテスト.txt";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["error"]         = 0;
        $value["size"]          = 1978;
        //
        $this->assertSame(true, $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]));
    }
    
    public function test_filecheck_no_file()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "ファイルアップロードテスト.txt";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["size"]          = 100000000;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $value["error"]         = UPLOAD_ERR_NO_FILE;
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("[テスト] はファイルが選択されていません。[code: {$value["error"]}]");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
    public function test_filecheck_over_filesize_limited()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "ファイルアップロードテスト.txt";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["size"]          = 100000000;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $value["error"]         = UPLOAD_ERR_INI_SIZE;
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("ファイルサイズが大きすぎます。[code: {$value["error"]}]");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
        //
        $value["error"]         = UPLOAD_ERR_FORM_SIZE;
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("ファイルサイズが大きすぎます。[code: {$value["error"]}]");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
    public function test_filecheck_over_unknown()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "ファイルアップロードテスト.txt";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["size"]          = 100000000;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $value["error"]         = UPLOAD_ERR_EXTENSION;
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("[{$label}] は正常にアップロード出来ませんでした。[code: {$value["error"]}]");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
    public function test_filecheck_file_zero()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "ファイルアップロードテスト.txt";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["error"]         = UPLOAD_ERR_OK;
        $value["size"]          = 0;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("[{$label}] は正常にアップロード出来ませんでした。ファイルサイズが 0 です。");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
    public function test_filecheck_uploaded_filename_is_empty()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["error"]         = UPLOAD_ERR_OK;
        $value["size"]          = 1978;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("アップロードされたファイルのファイル名が空です。[{$label}]");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
    public function test_filecheck_dot_file()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = ".";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["error"]         = UPLOAD_ERR_OK;
        $value["size"]          = 1978;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("先頭または末尾が . の名前のファイルのアップロードは許可されていません。[{$label}]");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
    public function test_filecheck_dot_begining_file()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = ".test";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["error"]         = UPLOAD_ERR_OK;
        $value["size"]          = 1978;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("先頭または末尾が . の名前のファイルのアップロードは許可されていません。[{$label}]");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
    public function test_filecheck_dot_ending_file()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "test.";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["error"]         = UPLOAD_ERR_OK;
        $value["size"]          = 1978;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("先頭または末尾が . の名前のファイルのアップロードは許可されていません。[{$label}]");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
    public function test_filecheck_containts_slash()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "test/testfile.txt";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["error"]         = UPLOAD_ERR_OK;
        $value["size"]          = 1978;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("ファイル名に / を含むファイルのアップロードは許可されていません。[{$label}]");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
    public function test_filecheck_containts_5c()
    {
        $key        = "test";
        $conditions = [
            "type" => "file", "min" => PHP_INT_MIN, "max" => PHP_INT_MAX, 
            "auto_correct" => true, "filter" => null, "trim" => UME::TRIM_ALL, "null_byte" => false,
            "method" => UME::FILES, "require" => true
        ];
        $type       = $conditions["type"];
        $response   = $this->get_response();
        $value                  = [];
        $value["name"]          = "test\\testfile.txt";
        $value["type"]          = "text/plain";
        $value["real_type"]     = "text/plain";
        $value["tmp_name"]      = __DIR__ . "/folder/upload_test_file.txt";
        $value["error"]         = UPLOAD_ERR_OK;
        $value["size"]          = 1978;
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("ファイル名に \\ を含むファイルのアップロードは許可されていません。[{$label}]");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
}
