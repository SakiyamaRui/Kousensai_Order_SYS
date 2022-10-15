<?php
    require_once(dirname(__DIR__).'/main.php');

    function stockCheck($requestList, $isLock = false, $DB = null) {
        // 在庫不足している商品を記録
        $isAllCorrect = true;           // 在庫不足が1件でもあるか
        $inventoryShortage = Array();   // 在庫不足の商品を記録

        // DBへアクセス
        $DB_Flag = false;
        if ($DB == null) {
            $DB = DB_Connect();
        }

        $getRequestDataList = array_unique(array_column($requestList, 'product_id'));

        // 在庫取得する商品の取得
        $options_stock = getOptionData($getRequestDataList, 1);
        $product_name_list = getProductName($getRequestDataList, $DB);;

        // 在庫数の確認
        foreach($requestList as $key => $val) {
            $product_name = $product_name_list[$val['product_id']];
            // 全体の在庫があるかを確認
            if (isset($options_stock[$key]['全体の在庫'])) {
                $limit = $options_stock[$key]['全体の在庫'];

                if (($limit - $val['quantity']) < 0) {
                    // 在庫切れ
                    $isAllCorrect = false;
                    if (!is_array($inventoryShortage[$key])) $inventoryShortage[$key] = Array('msg' => 'Sales-limit', 'product_name' => $product_name);
                    continue;
                }
            }

            //各オプションの確認
            foreach($val['options'] as $option_name => $select) {
                // オプションの情報を取得
                if (!isset($options_stock[$val['product_id']])) continue;
                $option_value_index = array_search($select, array_column($options_stock[$val['product_id']][$option_name]['option_values'], 'value'));
                $stock = $options_stock[$val['product_id']][$option_name]['option_values'][$option_value_index]['stock'];

                if (($stock - $val['quantity']) < 0) {
                    // 一部オプションの在庫切れ
                    $isAllCorrect = false;
                    if (!isset($inventoryShortage[$key])) $inventoryShortage[$key] = '';
                    if (!is_array($inventoryShortage[$key])) $inventoryShortage[$key] = Array('msg' => 'option-limit', 'product_name' => $product_name,'option' => Array());
                    array_push($inventoryShortage[$key]['option'], Array('option_name' => $option_name, 'value' => $select));
                }
            }
        }

        if ($DB_Flag) {
            unset($DB);
        }

        if ($isAllCorrect) {
            return true;
        }else{
            return $inventoryShortage;
        }
    }
