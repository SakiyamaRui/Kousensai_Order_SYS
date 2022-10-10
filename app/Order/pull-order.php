<?php
    function userOderList($token, $DB = null) {
        $DB_flag = false;

        if ($DB == null) {
            $DB = DB_Connect();
            $DB_flag = true;
        }

        // DBで検索
        $sql = "SELECT 
                    `token`,
                    `order_id`,
                    `confirmed_order_flag`,
                    `order_time`
                FROM
                    ORDER_SYS_DB.`T_ORDER_INFORMATION_MAIN`
                WHERE
                    `session_token` = :session_token
                ORDER BY
                    `order_time` ASC;";
        $sql = $DB -> prepare($sql);

        $sql -> bindValue(':session_token', $token, PDO::PARAM_STR);
        $sql -> execute();

        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

        if (count($record) == 0 || $record == false) {
            if ($DB_flag) {
                unset($DB);
            }

            return Array();
        }

        // フォーマットを変更する
        $order_list = Array();
        foreach ($record as $val) {
            $status = '支払い待ち';

            if ($val['confirmed_order_flag'] == 1) {
                $status = '注文確定済み';

                $status = orderStatus($val['order_id'], $DB)['status'];
            }

            array_push($order_list, Array(
                'token' => $val['token'],
                'order_id' => $val['order_id'],
                'order_time' => $val['order_time'],
                'status' => $status
            ));
        }

        if ($DB_flag) {
            unset($DB);
        }

        return $order_list;

    }

    function orderStatus($order_id, $DB = null) {
        $DB_flag = false;

        if ($DB == null) {
            $DB = DB_Connect();
            $DB_flag = true;
        }

        // 注文の商品一覧を取得
        $sql = "SELECT
                    `t_order_information_detail`.`product_id`,
                    `passed_flag`,
                    `made_flag`,
                    `t_product_information`.`store_id`
                FROM
                    ORDER_SYS_DB.`t_order_information_detail`
                INNER JOIN ORDER_SYS_DB.`t_product_information` ON `t_order_information_detail`.`product_id` = `t_product_information`.`product_id`
                WHERE
                    `order_id` = :order_id";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':order_id', $order_id, PDO::PARAM_STR);

        $sql -> execute();
        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

        $product_status = Array();
        $passed = true;
        foreach($record as $val) {
            if (!isset($product_status[$val['store_id']])) {
                $product_status[$val['product_id']] = true;
            }

            if ($val['passed_flag'] === 0) {
                $passed = false;
            }

            if ($val['made_flag'] === 0) {
                $product_status[$val['product_id']] = false;
            }
        }

        if ($DB_flag) {
            unset($DB);
        }

        $false = in_array(false, $product_status);
        $true = in_array(true, $product_status);

        if ($true == true && $false == false) {
            // すべて受取可能
            
            // 受け取り済みか
            if ($passed) {
                return Array('status' => '受け取り済み');
            }else{
                return Array('status' => '受取可能');
            }
        }

        if ($true == true && $false == true) {
            // 一部受取可能
            return Array('status' => '一部受取可能', 'shop_list' => $product_status);
        }

        if ($true == false && $false == true) {
            // 注文確定済み
            return Array('status' => '注文確定済み');
        }
    }