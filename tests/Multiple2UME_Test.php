<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ルーター
 * Start Date  : 2021/09/02
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\basis\ume\UME;
use \mikisan\core\exception\UMEException;
use \mikisan\pine\app\Multiple2UME;

require_once __DIR__ . "/../vendor/autoload.php";
$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/tests/TestCaseTrait.php";

Autoload::register(__DIR__ . "/../src", true);
Autoload::register(__DIR__ . "/folder", true);

class Multiple2UME_Test extends TestCase
{
    use TestCaseTrait;
    
    private $ume;

    public function setUp(): void
    {
        $this->ume  = new Multiple2UME();
        //
        $_GET       = [];
    }
    
    public function test_getRules()
    {
        $rules          = $this->ume->getRules();
        $this->assertCount(1, $rules);
        $this->assertArrayHasKey("page[]", $rules);
    }
    
    public function test_validate_value()
    {
        $key            = "page[]";
        $prefix         = "page";
        $_GET[$prefix]     = ["123", "４５６", "７８９01"];
        $labels         = $this->ume->getLabels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        //
        $result         = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SIMPLE, $result->type);
        //
        $this->assertSame(false, $result->has_error);
        $this->assertCount(3, $result->info->src[$prefix]);
        $this->assertCount(3, $result->data[$prefix]);
        $this->assertSame("123", $result->info->src[$prefix][0]);
        $this->assertSame("４５６", $result->info->src[$prefix][1]);
        $this->assertSame("７８９01", $result->info->src[$prefix][2]);
        $this->assertSame(123, $result->data[$prefix][0]);
        $this->assertSame(456, $result->data[$prefix][1]);
        $this->assertSame(78901, $result->data[$prefix][2]);
    }
    
    public function test_validate_value_not_array()
    {
        $key            = "page[]";
        $prefix         = "page";
        $_GET[$prefix]  = "123";
        $data_type      =gettype($_GET[$prefix]);
        $labels         = $this->ume->getLabels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        //
        $this->expectException(UMEException::class);
        $this->expectExceptionMessage("バリデーションルール {$key} として渡された値が配列ではありません。[type: {$data_type}]");
        //
        $result         = $this->ume->validate()->getResult();
    }
    
    public function test_validate_value_empty()
    {
        $key            = "page[]";
        $prefix         = "page";
        $_GET[$prefix]  = "";
        $labels         = $this->ume->getLabels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        //
        $result         = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SIMPLE, $result->type);
        //
        $this->assertSame(true, $result->has_error);
        $this->assertSame("[$label] は必須項目です。", $result->info->error[$prefix][0]);
        $this->assertSame("", $result->info->src[$prefix]);
        $this->assertSame(null, $result->data[$prefix]);
    }
    
    public function  test_validate_value_force_empty()
    {
        $key            = "page[]";
        $prefix         = "page";
        $_GET[$prefix]     = ["123", "", "７８９01"];
        $labels         = $this->ume->getLabels();
        $label          = $labels["ja_JP"][$key] ?? $key;
        //
        $result         = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SIMPLE, $result->type);
        //
        $this->assertSame(true, $result->has_error);
        $label          = $labels["ja_JP"][$key] ?? $key;
        $this->assertSame("[{$label}:1] は必須項目です。", $result->info->error[$prefix][0]);
        $this->assertCount(1,            $result->info->offset[$prefix]);
        $this->assertSame(1,            $result->info->offset[$prefix][0]);
        //
        $this->assertCount(3, $result->info->src[$prefix]);
        $this->assertCount(3, $result->data[$prefix]);
        $this->assertSame("123", $result->info->src[$prefix][0]);
        $this->assertSame("", $result->info->src[$prefix][1]);
        $this->assertSame("７８９01", $result->info->src[$prefix][2]);
        $this->assertSame(123, $result->data[$prefix][0]);
        $this->assertSame("", $result->data[$prefix][1]);
        $this->assertSame(78901, $result->data[$prefix][2]);
    }
    
    public function test_validate_dataset_force_empty()
    {
        $key            = "page[]";
        $prefix         = "page";
        $dataset        = [
                            ["page" => ["123", "４５６", "７８９01"]],
                            ["page" => ["５３01", "3785", "２３"]],
                            ["page" => ["７６５", "", 89012]]
                        ];
        $this->ume->dataset($dataset);
        $labels     = $this->ume->getLabels();
        $label      = $labels["ja_JP"][$prefix] ?? $key;
        //
        $result     = $this->ume->validate()->getResult();
        //
        $this->assertSame(UME::DATA_SET, $result->type);
        //
        $this->assertSame(true, $result->has_error);
        $label          = $labels["ja_JP"][$key] ?? $key;
        $this->assertSame("[{$label}:1] は必須項目です。", $result->info[2]->error[$prefix][0]);
        $this->assertCount(1,            $result->info[2]->offset[$prefix]);
        $this->assertSame(1,            $result->info[2]->offset[$prefix][0]);
        //
        $this->assertCount(3, $result->info[0]->src[$prefix]);
        $this->assertCount(3, $result->data[0][$prefix]);
        $this->assertCount(3, $result->info[1]->src[$prefix]);
        $this->assertCount(3, $result->data[1][$prefix]);
        $this->assertCount(3, $result->info[2]->src[$prefix]);
        $this->assertCount(3, $result->data[2][$prefix]);
        //
        $this->assertSame("123",        $result->info[0]->src[$prefix][0]);
        $this->assertSame("４５６",     $result->info[0]->src[$prefix][1]);
        $this->assertSame("７８９01",   $result->info[0]->src[$prefix][2]);
        $this->assertSame(123,          $result->data[0][$prefix][0]);
        $this->assertSame(456,          $result->data[0][$prefix][1]);
        $this->assertSame(78901,        $result->data[0][$prefix][2]);
        //
        $this->assertSame("５３01",     $result->info[1]->src[$prefix][0]);
        $this->assertSame("3785",      $result->info[1]->src[$prefix][1]);
        $this->assertSame("２３",       $result->info[1]->src[$prefix][2]);
        $this->assertSame(5301,          $result->data[1][$prefix][0]);
        $this->assertSame(3785,          $result->data[1][$prefix][1]);
        $this->assertSame(23,           $result->data[1][$prefix][2]);
        //
        $this->assertSame("７６５", $result->info[2]->src[$prefix][0]);
        $this->assertSame("", $result->info[2]->src[$prefix][1]);
        $this->assertSame(89012, $result->info[2]->src[$prefix][2]);
        $this->assertSame(765, $result->data[2][$prefix][0]);
        $this->assertSame("", $result->data[2][$prefix][1]);
        $this->assertSame(89012, $result->data[2][$prefix][2]);
    }
    
}
