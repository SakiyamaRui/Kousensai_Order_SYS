<?php
    require_once(dirname(__DIR__).'/main.php');

    function salesCalculation($DB) {
        $report = Array(
            'total' => 0
        );

        // 全体の売上の取得
        $sql = "SELECT
                    SUM(`order_total_price`)
                FROM
                    `T_ORDER_INFORMATION_MAIN`
                WHERE
                    `confirmed_order_flag` = 1;";
        $sql = $DB -> prepare($sql);
        $sql -> execute();
        $total_sum = $sql -> fetch(PDO::FETCH_ASSOC);

        if ($total_sum == false) {
            return false;
        }

        $report['total'] = (int)$total_sum['SUM(`order_total_price`)'];

        // 注文データの全件取得
        $sql = "SELECT
                    `order_id`,
                    `T_ORDER_INFORMATION_DETAIL`.`product_id`,
                    `T_PRODUCT_INFORMATION`.`product_name`,
                    `T_PRODUCT_INFORMATION`.`store_id`,
                    `quantity`,
                    `product_option`,
                    `unit_price`
                FROM
                    `T_ORDER_INFORMATION_DETAIL`
                LEFT JOIN
                    `T_PRODUCT_INFORMATION` ON `T_ORDER_INFORMATION_DETAIL`.`product_id` = `T_PRODUCT_INFORMATION`.`product_id`
                WHERE
                    `order_id` IN (SELECT
                            `order_id`
                        FROM
                            `T_ORDER_INFORMATION_MAIN`
                        WHERE
                            `confirmed_order_flag` = 1)";
        $sql = $DB -> prepare($sql);
        $sql -> execute();
        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

        if ($record == false) {
            return $report;
        }

        // オプションデータの取得
        $id_list = array_unique(array_column($record, 'product_id'));
        $option_data_base = getOptionData($id_list, 0, $DB);

        // 店舗名の取得
        $store_id_list = array_unique(array_column($record, 'store_id'));
        $store_name = getStoreName($store_id_list, $DB);

        // オプションデータのフォーマット
        $option_data = Array();
        foreach($option_data_base as $product_id => $option) {
            if (!isset($option_data[$product_id])) {
                $option_data = Array();
            }

            foreach ($option as $option_name => $val) {
                if (!isset($option_data[$product_id][$option_name])) {
                    $option_data[$product_id][$option_name] = Array();
                }

                foreach ($val['option_values'] as $option_value) {
                    $option_data[$product_id][$option_name][$option_value['value']] = $option_value['price'];
                }
            }
        }

        var_dump($id_list);

        // 演算
        $total_by_product = Array();
        $total_by_store = Array();
        $total = 0;

        foreach ($record as $order_item) {
            // 商品の値段を計算
            $price = $order_item['unit_price'];

            // オプションを含めた計算を行う
            $select_option = json_decode($order_item['product_option'], true);
            $opt_data = &$option_data[$order_item['product_id']];
            foreach ($select_option as $option_name => $option_value) {
                // echo "$option_name($option_value)".PHP_EOL;
                // var_dump($opt_data);
                if (!isset($opt_data[$option_name][$option_value])) {
                    continue;
                }

                $option_price = $opt_data[$option_name][$option_value];
                $price += $option_price;
            }
            unset($opt_data);

            // 各項目に追加
            if (!isset($total_by_product[$order_item['product_name']])) {
                $total_by_product[$order_item['product_name']] = 0;
            }

            $store_n = $store_name[$order_item['store_id']]['store_name'];
            if (!isset($total_by_store[$store_n])) {
                $total_by_store[$store_n] = 0;
            }

            $price *= $order_item['quantity'];

            echo "商品名: ".$order_item['product_name']." 個数: ".$order_item['quantity']." 値段: ".$price.PHP_EOL;

            $total += $price;
            $total_by_product[$order_item['product_name']] += $price;
            $total_by_store[$store_n] += $price;
        }

        // DBとの合計値を比較
        if ($total != $report['total']) {
            $report['isTotalMatch'] = false;
            $report['2ndTotalData'] = $total;
        }else{
            $report['isTotalMatch'] = false;
        }
        
        // レポートにデータの追加
        $report['totalByProduct'] = $total_by_product;
        $report['totalByStore'] = $total_by_store;

        return $report;
    }

    // $DB = DB_connect();
    // $data = salesCalculation($DB);
    // var_dump($data);