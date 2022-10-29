<?php

    $option_price_list = Array(
        '29589' => Array(
            'モンスターエナジー' => Array(
                '100' => 150,
                '50' => 0
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
                    `quantity`,
                    `passed_flag`,
                    `product_option`,
                    `unit_price`
                FROM
                    `T_ORDER_INFORMATION_DETAIL`
                INNER JOIN
                    `T_ORDER_INFORMATION_MAIN` ON `T_ORDER_INFORMATION_DETIAL`.`order_id` = `T_ORDER_INFORMATION_MAIN`.`order_id`
                INNER JOIN
                    `T_PRODUCT_OPTIONS` ON `T_ORDER_INFORMATION_DETAIL`.`product_id` = `T_PRODUCT_OPTIONS`.`product_id`
                WHERE
                    `T_ORDER_INFORMATION_MAIN`.`confirmed_order_flag` = '1'";
    }