<?php

/**
 * Project Name: mikisan-ware
 * Description : バリデーター
 * Start Date  : 2021/07/27
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\basis\ume;

class HIERARCHY
{
    
    public static function parse(Dto $dto, string $key, array $conditions) : void
    {
        preg_match("/\A([^%]+)_(%_)*%(\[\])?\z/u", $key, $key_array);           // 階層指定のキーからキーを取り出す
        $prefix     = $key_array[1];                                            // 第1サブパターン
        
        $req_array   = self::get_request_multi_array($key, $prefix, $conditions);

        // 必須入力チェック
        if($conditions['require'] === true && EX::empty($req_array))
        {
            $this->VE[$prefix][] = I18N::get("BaseUME.empty_value_in_requierd_column", [$conditions["name"]], "[:@0] は必須入力項目ですが、値が渡されませんでした。");
            $this->requestDatas[$prefix] = $req_array;
            return;
        }

        // 配列を再帰的にバリデーション
        $validation_type    = $this->getValidationType($conditions);
        $data_type          = $this->validators[$validation_type][0];
        $this->multiple_array_validate($req_array, $prefix, $conditions, $mode, $data_type);
 
        $this->requestDatas[$prefix] = $req_array;
        
        if($conditions["method"] === UME::FILES)
        {
            $dto->setPineUploads($prefix, $req_array);
        }
    }
    
    private function get_request_multi_array(string $key, string $prefix, array $conditions) : array
    {
        $req_array = [];
        
        $data = DATA_SOURCE::all($key, $conditions); // 指定されたmethodでのリクエストを全て取得

        foreach ($data as $key => $req)
        {
            // 階層連番パラメターではない
            if (!preg_match("/\A{$prefix}_(\d+_)*\d+(\[\])?\z/u", $key))    { continue; }

            // $keyから主キーを取り除く
            $key = preg_replace("/\A{$prefix}_/", "", $key);
            
            // $keyから配列ブラケットを取り除く
            $key = preg_replace("/\[\]\z/", "", $key);

            // 連番を配列に格納
            $serials = explode("_", $key);

            $temp_array = &$req_array;
            
            for($i = 0; $i < count($serials); $i++)
            {
                if($i === count($serials) - 1)
                {
                    if($conditions["method"] === UME::FILES)
                    {
                        if(empty($req))
                        {
                            $temp_array[$serials[$i]] = $req;
                        }
                        else if(isset($req["name"]) && is_array($req["name"]))
                        {
                            // FILESの配列を再構築した後シリアライズして格納
                            $temp_array[$serials[$i]] = serialize($this->rebuildFileInfoArray($req));
                        }
                        else
                        {
                            // 実MIMEタイプを取得して設定
                            $req["real_type"]  = MIME::getMIME($req["tmp_name"]);
                            
                            // シリアライズして保存
                            $temp_array[$serials[$i]] = serialize($req);
                        }
                    }
                    else
                    {
                        if($decode === UME::UNENT)
                        {
                            $req = $this->decodeEscapedHTML($req);
                        }
                        else if($decode === UME::BASE64)
                        {
                            $req = $this->decodeBase64($key, $req, $conditions);
                        }
                        $temp_array[$serials[$i]] = $req;
                    }
                }
                else
                {
                    if(!isset($temp_array[$serials[$i]]))
                    {
                        $temp_array[$serials[$i]] = [];
                    }
                }
                
                // 配列を参照渡しで再定義
                $temp_array = &$temp_array[$serials[$i]];
            }
            unset($temp_array);
        }

        return $req_array;
    }
    
    private function multiple_array_validate(array &$array, string $key, array $conditions, bool $mode, string $data_type) : void
    {
        $keybuffer = $key;
        foreach ($array as $serial => &$value)
        {
            $key .= "_{$serial}";
            if (is_array($value))
            {
                $this->multiple_array_validate($value, $key, $conditions, $mode, $data_type);
            }
            else
            {
                if($mode !== true)
                {
                    $req = $value;
                }
                else
                {
                    if($conditions["method"] === UME::FILES)
                    {
                        $value = unserialize($value); 
                    }
                    if(is_array($value) && !isset($value["name"]))
                    {
                        $req = array();
                        $idx = 0;
                        foreach($value as $serial => $val)
                        {
                            $req[$serial] = $this->doValidate($key, $val, $conditions);
                            $idx++;
                        }
                    }
                    else
                    {
                        $req = $this->doValidate($key, $value, $conditions);
                    }
                }
                $array[$serial] = $req;
                $this->requestDatas[$key] = $req;
            }
            $key = $keybuffer;
        }
        unset($value);
    }
}
