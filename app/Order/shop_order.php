<?php
    function getOrders($store_id, $DB) {
        //
        $sql = "SELECT
                    Detail.`order_item_id`,
                    Detail.`order_id`,
                    Detail.`quantity`,
                    Detail.`passed_flag`,
                    Detail.`made_flag`,
                    Detail.`product_option`,
                    Main.`order_time`,
                    Main.`pickup_now`,
                    Main.`pickup_time`,
                    Product.`product_name`
                FROM
                    `T_ORDER_INFORMATION_DETAIL` as Detail
                INNER JOIN
                    `T_ORDER_INFORMATION_MAIN` as Main ON Detail.`order_id` = Main.`order_id`
                INNER JOIN
                    `T_PRODUCT_INFORMATION` as Product ON Detail.`product_id` = Product.`product_id`
                WHERE
                    Main.`confirmed_order_flag` = 1 AND
                    Product.`store_id` = :store_id
                ORDER BY Main.`order_time` ASC";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':store_id', $store_id, PDO::PARAM_STR);
        $sql -> execute();
        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

        // フォーマットに治す
        $return_data = Array();

        foreach ($record as $val) {
            // 時間の取得
            if ($val['pickup_now'] == 1) {
                $time = '今すぐ';
            }else{
                $time = $val['pickup_time'];
            }

            // 初期フォーマット
            if (!isset($return_data[$time])) {
                $return_data[$time] = Array();
            }

            // 注文カラムの作成
            if (!isset($return_data[$time][$val['order_id']])) {
                $return_data[$time][$val['order_id']] = Array(
                    'passed' => true,
                    'created' => true,
                    'order_time' => $val['order_time'],
                    'items' => Array()
                );
            }

            if ($val['passed_flag'] == 0) {
                $return_data[$time][$val['order_id']]['passed'] = false;
            }

            if ($val['made_flag'] == 0) {
                $return_data[$time][$val['order_id']]['created'] = false;
            }

            // $col_p = &$return_data[$time][$val['order_id']];

            // カラムの作成
            $column = Array(
                'order_item_id' => $val['order_item_id'],
                'product_name' => $val['product_name'],
                'quantity' => $val['quantity'],
                'passed_flag' => ($val['passed_flag'] == 1)? true: false,
                'created' => ($val['made_flag'] == 1)? true: false,
                'product_option' => json_decode($val['product_option'], true)
            );

            // 追加
            array_push($return_data[$time][$val['order_id']]['items'], $column);
        }

        // すべて完成もしくは受け渡ししているものは削除
        foreach ($return_data as &$time_col) {
            foreach ($time_col as $order_id => $val) {
                if ($val['passed'] || $val['created']) {
                    // 要素の削除
                    unset($time_col[$order_id]);
                }
            }

            if (count($time_col) == 0) {
                unset($time_col);
            }
        }

        return $return_data;
    }

    function updateOrderItemCreated($list, $DB) {
        $isTrue = array_keys($list, true);
        $isFalse = array_keys($list, false);


        if (count($isTrue) != 0) {
            $inClause = substr(str_repeat(',?', count($isTrue)), 1);
            $sql = "UPDATE `T_ORDER_INFORMATION_DETAIL` SET `made_flag` = 1 WHERE `order_item_id` IN(%s);";
            $sql = $DB -> prepare(sprintf($sql, $inClause));
            $sql -> execute(array_values($isTrue));
        }

        if (count($isFalse) != 0) {
            $inClause = substr(str_repeat(',?', count($isFalse)), 1);
            $sql = "UPDATE `T_ORDER_INFORMATION_DETAIL` SET `made_flag` = 0 WHERE `order_item_id` IN(%s);";
            $sql = $DB -> prepare(sprintf($sql, $inClause));
            $sql -> execute(array_values($isFalse));
        }

        return true;
    }