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
        $options_list = getOptionData($getRequestDataList);
        
        // 在庫の取得・ロックするorder_idのリストを作成
        $order_id_list = Array();
        $option_key_id = Array();
        foreach($requestList as $item) {
            $product_info = $options_list[$item['product_id']];

            foreach($product_info as $option_name => $value) {
                if (!isset($item['options'][$option_name])) {
                    continue;
                }

                $index = array_search($item['options'][$option_name], array_column($value['option_values'], 'value'));

                if ($index === false) {
                    continue;
                }

                $option_key_id[$option_name] = $value['option_values'][$index]['option_id'];
                array_push($order_id_list, $value['option_values'][$index]['option_id']);
            }
        }

        if ($isLock == true && $DB_Flag == false) {
            $DB -> beginTransaction();
        }

        try{
            $stock_p = getStock_data_product($getRequestDataList, ($isLock == true && $DB_Flag == false)? true: false, $DB);
            $stock_o = getStock_data_option($order_id_list, ($isLock == true && $DB_Flag == false)? true: false, $DB);
            $product_name_list = getProductName($getRequestDataList, $DB);
        }catch (PDOException $e) {
            $DB -> rollback();
        }

        // 在庫数の確認
        if ($isLock == true && $DB_Flag == false) {
            $product = Array();
            $options = Array();
        }


        foreach($requestList as $val) {
            $product_name = $product_name_list[$val['product_id']];

            // 全体の在庫があるかを確認
            $index = array_search($val['product_id'], array_column($stock_p, 'product_id'));
            if ($index !== false) {
                $product_stock = $stock_p[$index]['current_stock'];

                if (($product_stock - $val['quantity']) < 0) {
                    // 在庫切れ
                    $isAllCorrect = false;
                    if (!is_array($inventoryShortage[$key])) $inventoryShortage[$key] = Array('msg' => 'Sales-limit', 'product_name' => $product_name);
                    continue;
                }else{
                    if ($isLock == true && $DB_Flag == false) {
                        $product[$val['product_id']] = $product_stock - $val['quantity'];
                    }
                }
            }

            //各オプションの確認
            foreach($val['options'] as $option_name => $select) {
                // オプションの情報を取得
                $option_id = $option_key_id[$option_name];
                
                // オプションの在庫を取得
                $index = array_search($option_id, array_column($stock_o, 'option_id'));
                if ($index !== false) {
                    $option_stock = ($stock_o[$index]['current_stock'] == -1)? INF: $stock_o[$index]['current_stock'];
                    
                    if (($option_stock - $val['quantity']) < 0) {
                        $isAllCorrect = false;

                        if (!isset($inventoryShortage[$key])) $inventoryShortage[$key] = '';
                        if (!is_array($inventoryShortage[$key])) $inventoryShortage[$key] = Array('msg' => 'option-limit', 'product_name' => $product_name,'option' => Array());
                        array_push($inventoryShortage[$key]['option'], Array('option_name' => $option_name, 'value' => $select));
                    }else{
                        if ($isLock == true && $DB_Flag == false) {
                            $options[$option_id] = ($option_stock - $val['quantity'] == INF)? -1: $option_stock - $val['quantity'];
                        }
                    }
                }
            }
        }

        if ($DB_Flag) {
            unset($DB);
        }

        // ロックされている場合は、在庫を減らす
        if ($isLock == true && $DB_Flag == false) {
            $sql = "";

            // Product
            foreach($product as $key => $val) {
                $sql .= "UPDATE `T_STOCKS` SET `current_stock` = ${val} WHERE `product_id` = '${key}'".PHP_EOL;
            }
            
            // Option
            foreach($options as $key => $val) {
                $sql .= "UPDATE `T_STOCKS_OPTION` SET `current_stock` = ${val} WHERE `option_id` = ${key}".PHP_EOL;
            }

            $sql = $DB -> prepare($sql);
            $sql -> execute();

            $DB -> commit();
        }

        if ($isAllCorrect) {
            return true;
        }else{
            return $inventoryShortage;
        }
    }

    function unlockDB($product_id, $option_id, $DB) {
        // Product
        $sql = "";
    }

    function getStock_data_option($option_id_list, $isLock, $DB) {
        $inClause = substr(str_repeat(',?', count($option_id_list)), 1);
        $sql = "SELECT
                    `option_id`,
                    `current_stock`
                FROM
                    `T_STOCKS_OPTION`
                WHERE
                    `option_id` IN(%s)";
        if ($isLock) {
            $sql .= "FOR UPDATE;";
        }
        $sql = $DB -> prepare(sprintf($sql, $inClause));
        $sql -> execute($option_id_list);
        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

        if ($record == false) {
            return Array();
        }

        return $record;
    }

    function getStock_data_product($product_id_list, $isLock, $DB) {
        $inClause = substr(str_repeat(',?', count($product_id_list)), 1);
        $sql = "SELECT
                    `product_id`,
                    `current_stock`
                FROM
                    `T_STOCKS`
                WHERE
                    `product_id` IN(%s)";
        if ($isLock) {
            $sql .= "FOR UPDATE;";
        }
        $sql = $DB -> prepare(sprintf($sql, $inClause));
        $sql -> execute($product_id_list);
        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

        if ($record == false) {
            return Array();
        }

        return $record;
    }
