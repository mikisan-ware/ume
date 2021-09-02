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
use \mikisan\core\exception\UMEException;
use \mikisan\pine\app\ChildUME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class ChildUME_Test extends TestCase
{
    use TestCaseTrait;
    
    private $ume;

    public function setUp(): void
    {
        $this->ume  = new ChildUME();
        //
        $_GET       = [];
        $_POST      = [];
        $_COOKIE    = [];
        $_FILES     = [];
    }
    
    public function test_getRules()
    {
        $rules          = $this->ume->getRules();
        $this->assertCount(4, $rules);
        $this->assertArrayHasKey("page", $rules);
        $this->assertArrayHasKey("test2", $rules);
        $this->assertArrayHasKey("test3", $rules);
        $this->assertArrayHasKey("test[]", $rules);
    }
    
    public function test_validate_value()
    {
        $key            = "page";
        $_GET[$key]     = "123";
        $labels         = $this->ume->getLabels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        //
        $result         = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SIMPLE, $result->type);
        //
        $this->assertSame(false, $result->has_error);
        $this->assertSame("123", $result->info->src[$key]);
        $this->assertSame(123, $result->data[$key]);
    }
    
    public function test_validate_value_empty()
    {
        $key            = "page";
        $_GET[$key]     = "";
        $labels         = $this->ume->getLabels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        //
        $result         = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SIMPLE, $result->type);
        //
        $this->assertSame(true, $result->has_error);
        $this->assertSame("[$label] は必須項目です。", $result->info->error["page"][0]);
        $this->assertSame("", $result->info->src[$key]);
        $this->assertSame("", $result->data[$key]);
    }
    
    public function test_validate_value_undefined_method()
    {
        $key            = "page";
        $_POST[$key]    = "123";
        $labels         = $this->ume->getLabels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        //
        $result         = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SIMPLE, $result->type);
        //
        $this->assertSame(true, $result->has_error);
        $this->assertSame("[$label] は必須項目です。", $result->info->error["page"][0]);
        $this->assertSame(null, $result->info->src[$key]);
        $this->assertSame(null, $result->data[$key]);
    }
    
    public function test_validate_array()
    {
        $key            = "test[]";
        $prefix         = "test";
        $_GET["page"]   = "12345";
        $_GET[$prefix]  = ["123", "４５６", 789];
        $labels         = $this->ume->getLabels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        //
        $result         = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SIMPLE, $result->type);
        //
        $this->assertSame(false, $result->has_error);
        $this->assertContains("123", $result->info->src[$prefix]);
        $this->assertContains("４５６", $result->info->src[$prefix]);
        $this->assertContains(789, $result->info->src[$prefix]);
        $this->assertContains(123, $result->data[$prefix]);
        $this->assertContains(456, $result->data[$prefix]);
        $this->assertContains(789, $result->data[$prefix]);
    }
    
    public function test_validate_dataset()
    {
        $key        = "page";
        $dataset    = [
                        ["page" => "１２３４５", "test2" => "ＡＢＣＤＥＦＧ", "test3" => 12345],
                        ["page" => "４５６７８", "test2" => "ｈｉｊｋｌｍｎ", "test3" => 45678],
                        ["page" => "７８９０１", "test2" => "ｎｏｐｉＲＳＴ", "test3" => 78901]
                    ];
        $this->ume->dataset($dataset);
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$key] ?? $key;
        //
        $result     = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SET, $result->type);
        //
        $this->assertSame(false, $result->has_error);
    }
    
    public function test_validate_dataset_with_validation_error()
    {
        $dataset    = [
                        ["page" => "１２３４５", "test2" => "ＡＢＣＤＥＦＧ", "test3" => 12345],
                        ["page" => "ｈｉｊｋｌｍｎ", "test2" => "４５６７８", "test3" => 45678],
                        ["page" => "７８９０１", "test2" => "ｎｏｐｉＲＳＴ", "test3" => 98765]
                    ];
        $this->ume->dataset($dataset);
        $labels     = $this->ume->getLabels();
        //
        $result     = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SET, $result->type);
        //
        $this->assertSame(true, $result->has_error);
        $this->assertCount(0, $result->info[0]->error);
        $this->assertSame(12345, $result->data[0]["page"]);
        $this->assertSame("ABCDEFG", $result->data[0]["test2"]);
        $this->assertSame("12345", $result->data[0]["test3"]);
        //
        $this->assertCount(2, $result->info[1]->error);
        $key        = "page";
        $label      = $labels["ja_JP"][$key] ?? $key;
        $this->assertSame("[{$label}] は整数でなければなりません。", $result->info[1]->error[$key][0]);
        $this->assertSame("ｈｉｊｋｌｍｎ", $result->info[1]->src[$key]);
        $key        = "test2";
        $label      = $labels["ja_JP"][$key] ?? $key;
        $this->assertSame("[{$label}] には英字以外が含まれています。", $result->info[1]->error[$key][0]);
        $this->assertSame("４５６７８", $result->info[1]->src[$key]);
        $this->assertSame("ｈｉｊｋｌｍｎ", $result->data[1]["page"]);
        $this->assertSame("４５６７８", $result->data[1]["test2"]);
        $this->assertSame("45678", $result->data[1]["test3"]);
        //
        $this->assertCount(0, $result->info[2]->error);
        $this->assertSame(78901, $result->data[2]["page"]);
        $this->assertSame("nopiRST", $result->data[2]["test2"]);
        $this->assertSame("98765", $result->data[2]["test3"]);
    }
    
    public function test_validate_dataset_getDataset()
    {
        $key        = "page";
        $dataset    = [
                        ["page" => "１２３４５", "test2" => "ＡＢＣＤＥＦＧ", "test3" => 12345],
                        ["page" => "４５６７８", "test2" => "ｈｉｊｋｌｍｎ", "test3" => 45678],
                        ["page" => "７８９０１", "test2" => "ｎｏｐｉＲＳＴ", "test3" => 78901]
                    ];
        $result     = $this->ume->dataset($dataset)->getDataset();
        //
        $this->assertSame("array", gettype($result));
        $this->assertCount(3, $result);
        $this->assertSame(45678, $result[1]["test3"]);
    }
    
    public function test_validate_dataset_non_array()
    {
        $key        = "page";
        $dataset    = "value";
        $data_type  = gettype($dataset);
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("UME::dataset() に渡された入力値が配列ではありません。[type: {$data_type}]");
        $this->ume->dataset($dataset);
    }
    
    public function test_validate_value_another_encoding_no_convert()
    {
        $key            = "page";
        $value          = "１２３４５";
        $_GET[$key]     = mb_convert_encoding($value, "cp932", "UTF-8");
        $labels         = $this->ume->getLabels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        //
        $result         = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SIMPLE, $result->type);
        $this->assertSame(true, $result->has_error);
    }
    
    public function test_validate_value_another_encoding_converted()
    {
        $key            = "page";
        $value          = "１２３４５";
        $_GET[$key]     = mb_convert_encoding($value, "cp932", "UTF-8");
        $labels         = $this->ume->getLabels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        //
        $result         = $this->ume->setFromEncoding("cp932")->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SIMPLE, $result->type);
        $this->assertSame(false, $result->has_error);
        $this->assertSame($_GET[$key], $result->info->src[$key]);
        $this->assertSame(12345, $result->data[$key]);
    }
    
    public function test_conflict_register_types()
    {
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("register_types() に与えられたタイプ int は既に規定されています。");
        //
        $this->ume->conflict_register_types();
    }
    
    public function test_conflict_register_filters()
    {
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("register_filters() に与えられたフィルタ int は既に規定されています。");
        //
        $this->ume->conflict_register_filters();
    }
    
    public function test_conflict_register_closers()
    {
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("register_closers() に与えられた事後フィルタ base64 は既に規定されています。");
        //
        $this->ume->conflict_register_closers();
    }
    
    public function test_conflict_register_rules()
    {
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("register_rules() に与えられたルール page は既に規定されています。");
        //
        $this->ume->conflict_register_rules();
    }
    
    public function test_conflict_register_labels()
    {
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("register_labels() に与えられたラベル ja_JP.page は既に規定されています。");
        //
        $this->ume->conflict_register_labels();
    }
    
}
