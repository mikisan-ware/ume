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

class FILE_Test_non_debuging_mode extends TestCase
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
        $labels     = $this->ume->get_labels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("[{$label}] は正規のアップロードファイルではありません。");
        $this->callMethod($this->class_name, "filecheck", [$this->ume, $value, $key]);
    }
    
}
