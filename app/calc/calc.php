<?php

    $option_price_list = Array(
        '29589' => Array(
            'ドリンク' => Array(
                'モンスターエナジー' => Array(
                    '100' => 150,
                    '50' => 0
                )
            )
        )
    );

    $report = Array();
    function total_calc($DB) {
        // 注文の合計を取得
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


        // 会計済みの注文データの全件取得
        $sql = "SELECT
                    `order_item_id`,
                    `T_ORDER_INFORMATION_DETAIL`.`order_id`,
                    `product_name`,
                    `T_ORDER_INFORMATION_DETAIL`.`product_id`,
                    `store_name`,
                    `quantity`,
                    `passed_flag`,
                    `product_option`,
                    `unit_price`
                FROM
                    `T_ORDER_INFORMATION_DETAIL`
                INNER JOIN
                    `T_ORDER_INFORMATION_MAIN` ON `T_ORDER_INFORMATION_DETAIL`.`order_id` = `T_ORDER_INFORMATION_MAIN`.`order_id`
                INNER JOIN
                    `T_PRODUCT_INFORMATION` ON `T_ORDER_INFORMATION_DETAIL`.`product_id` = `T_PRODUCT_INFORMATION`.`product_id`
                INNER JOIN
                    `T_STORE_INFORMATION` ON `T_PRODUCT_INFORMATION`.`store_id` = `T_STORE_INFORMATION`.`store_id`
                WHERE
                    `confirmed_order_flag` = 1";
        $sql = $DB -> prepare($sql);
        $sql -> execute();
        $order_data = $sql -> fetchAll(PDO::FETCH_ASSOC);

        // 計算
        $normal_sum = Array('all' => Array('len' => 0, 'price' => 0), 'passed' => Array('len' => 0, 'price' => 0));
        $item_sum = Array('all' => Array(), 'passed' => Array());
        $store_sum = Array('all' => Array(), 'passed' => Array());
        foreach ($order_data as $item) {
            // 値段計算
            // 単価の取得
            $unit_price = (int) $item['unit_price'];

            // オプションの値段調整
            if (isset($option_price_list[$item['product_id']])) {
                foreach(json_decode($item['product_option'], true) as $option_name => $opt_val) {
                    if (isset($option_price_list[$item['product_id']][$opt_val][$item['unit_price']])) {
                        $unit_price += isset($option_price_list[$item['product_id']][$opt_val][$item['unit_price']]);
                    }
                }
            }

            // 個数をかける
            $price = $unit_price * $item['quantity'];

            // 足す
            $normal_sum['all']['len'] += 1;
            $normal_sum['all']['price'] += $price;

            // store
            if (!isset($store_sum['all'][$item['store_name']])) {
                $store_sum['all'][$item['store_name']] = Array('len' => 0,'price' => 0);
            }
            $store_sum['all'][$item['store_name']]['len']++;
            $store_sum['all'][$item['store_name']]['price'] += $price;

            // 商品・値段毎
            // 商品ごと
            if (!isset($item_sum['passed'][$item['product_name']])) {
                $item_sum['passed'][$item['product_name']] = Array();
            }

            // 商品の値段変更ごと
            if (!isset($item_sum['passed'][$item['product_name']][$unit_price])) {
                $item_sum['passed'][$item['product_name']][$unit_price] = Array('len' => 0, 'price' => 0);
            }
            $item_sum['passed'][$item['product_name']][$unit_price]['len']++;
            $item_sum['passed'][$item['product_name']][$unit_price]['price'] += $price;

            // 受け渡し済みの記録
            if ($item['passed_flag'] == 0) {
                continue;
            }

            //
            // 足す
            $normal_sum['passed']['len']++;
            $normal_sum['passed']['price'] += $price;

            // store
            if (!isset($store_sum['passed'][$item['store_name']])) {
                $store_sum['passed'][$item['store_name']] = Array('len' => 0,'price' => 0);
            }
            $store_sum['passed'][$item['store_name']]['len']++;
            $store_sum['passed'][$item['store_name']]['price'] += $price;

            // 商品・値段毎
            // 商品ごと
            if (!isset($item_sum['passed'][$item['product_name']])) {
                $item_sum['passed'][$item['product_name']] = Array();
            }

            // 商品の値段変更ごと
            if (!isset($item_sum['passed'][$item['product_name']][$unit_price])) {
                $item_sum['passed'][$item['product_name']][$unit_price] = Array('len' => 0, 'price' => 0);
            }
            $item_sum['passed'][$item['product_name']][$unit_price]['len']++;
            $item_sum['passed'][$item['product_name']][$unit_price]['price'] += $price;
        }

        return Array(
            'total' => $normal_sum,
            'product' => $item_sum,
            'store' => $store_sum
        );
    }