<?php
    require_once(dirname(__DIR__).'\main.php');

    function stockCheck($requestList, $isLock = false) {
        // 在庫不足している商品を記録
        $isAllCorrect = true;           // 在庫不足が1件でもあるか
        $inventoryShortage = Array();   // 在庫不足の商品を記録

        // DBへアクセス
        $DB = DB_Connect();

        $getRequestDataList = array_keys($requestList);

        // 在庫取得する商品の取得
        $options_stock = getOptionData($getRequestDataList, 1);

        // 在庫数の確認
        foreach($requestList as $key => $val) {
            // 全体の在庫があるかを確認
            if (isset($options_stock[$key]['全体の在庫'])) {
                $limit = $options_stock[$key]['全体の在庫'];

                if (($limit - $val['quantity']) < 0) {
                    // 在庫切れ
                    $isAllCorrect = false;
                    if (!is_array($inventoryShortage[$key])) $inventoryShortage[$key] = Array('msg' => 'Sales-limit');
                    continue;
                }
            }

            // 各オプションの確認
            foreach($val['options'] as $option_name => $select) {
                // オプションの情報を取得
                $option_value_index = array_search($select, array_column($options_stock[$key][$option_name]['option_values'], 'value'));
                $stock = $options_stock[$key][$option_name]['option_values'][$option_value_index]['stock'];

                if (($stock - $val['quantity']) < 0) {
                    // 一部オプションの在庫切れ
                    if (!is_array($inventoryShortage[$key])) $inventoryShortage[$key] = Array('msg' => 'option-limit', 'option' => Array());
                    array_push($inventoryShortage[$key]['option'], $option_name);
                }
            }
        }

        unset($DB);

        if ($isAllCorrect) {
            return true;
        }else{
            return $inventoryShortage;
        }
    }
