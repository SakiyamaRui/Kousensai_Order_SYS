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
                    `T_ORDER_INFORMATION_MAIN`
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
                    `T_ORDER_INFORMATION_DETAIL`.`product_id`,
                    `passed_flag`,
                    `made_flag`,
                    `T_PRODUCT_INFORMATION`.`store_id`
                FROM
                    `T_ORDER_INFORMATION_DETAIL`
                INNER JOIN `T_PRODUCT_INFORMATION` ON `T_ORDER_INFORMATION_DETAIL`.`product_id` = `T_ORDER_INFORMATION_DETAIL`.`product_id`
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

            if ($val['passed_flag'] == 0) {
                $passed = false;
            }

            if ($val['made_flag'] == 0) {
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

    function orderItems($order_id, $DB = null) {
        $DB_flag = false;

        if ($DB == null) {
            $DB = DB_Connect();
            $DB_flag = true;
        }

        $sql = "SELECT
                    `T_PRODUCT_INFORMATION`.`product_name`,
                    `product_option`
                FROM
                    `T_ORDER_INFORMATION_DETAIL`
                INNER JOIN `T_PRODUCT_INFORMATION` ON `T_ORDER_INFORMATION_DETAIL`.`product_id` = `T_PRODUCT_INFORMATION`.`product_id`
                WHERE
                    `order_id` = :order_id";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':order_id', $order_id, PDO::PARAM_STR);

        $sql -> execute();
        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

        if ($record == false) {
            return Array();
        }

        if (count($record) == 0) {
            return Array();
        }
        
        return $record;
    }

    function orderDetailShop($type, $id, $DB = null, $product_id_list = false, $sort = false) {
        //
        $DB_flag = false;

        if ($DB == null) {
            $DB = DB_Connect();
            $DB_flag = true;
        }

        $sql = "SELECT
                    `token`,
                    `order_id`,
                    `confirmed_order_flag`
                FROM
                    `T_ORDER_INFORMATION_MAIN`
                WHERE ";
        if ($type == 'token') {
            $sql .= "`token` = :value";
        }else{
            $sql .= "`order_id` = :value";
        }

        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':value', $id, PDO::PARAM_STR);
        $sql -> execute();
        $order_data = $sql -> fetch(PDO::FETCH_ASSOC);

        if ($order_data == false) {
            return false;
        }

        try {
            $pay = ($order_data['confirmed_order_flag'] == 1)? true: false;
            $order_id = $order_data['order_id'];
        } catch (Exception $e) {
            return false;
        }

        // 注文した商品の一覧を取得
        $sql = "SELECT
                    `order_item_id`,
                    `T_ORDER_INFORMATION_DETAIL`.`product_id`,
                    `quantity`,
                    `passed_flag`,
                    `made_flag`,
                    `product_option`,
                    `unit_price`,
                    `T_PRODUCT_INFORMATION`.`product_name`
                FROM
                    `T_ORDER_INFORMATION_DETAIL`
                INNER JOIN `T_PRODUCT_INFORMATION` ON `T_ORDER_INFORMATION_DETAIL`.`product_id` = `T_PRODUCT_INFORMATION`.`product_id`
                WHERE
                    `order_id` = :order_id";
        if ($sort) {
            $sql .= " AND
            `T_PRODUCT_INFORMATION`.`store_id` = :store_id";

        }
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':order_id', $order_id, PDO::PARAM_STR);
        if ($sort) {
            session::start();
            $sql -> bindValue(':store_id', $_SESSION['store_id'], PDO::PARAM_STR);
        }
        $sql -> execute();
        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);
        
        $return_data = Array();
        $product_id_u_list = Array();
        foreach ($record as &$val) {
            // 受け渡し済み
            if ($val['passed_flag'] == 1) {
                $status = '完了';
            }

            // 調理完了
            if ($val['made_flag'] == 1 && $val['passed_flag'] == 0) {
                $status = '調理済';
            }

            // 確定済み
            if ($val['passed_flag'] == 0 && $val['made_flag'] == 0) {
                $status = '調理中';
            }

            if (!$pay) {
                $status = '未払い';
            }

            array_push($product_id_u_list, $val['product_id']);

            array_push($return_data, Array(
                'id' => $val['order_item_id'],
                'status' => $status,
                'product_id' => $val['product_id'],
                'product_name' => $val['product_name'],
                'quantity' => $val['quantity'],
                'product_option' => json_decode($val['product_option'], true),
                'passed' => ($val['passed_flag'] == 1)? true: false,
                'unit_price' => $val['unit_price']
            ));
        }

        if ($DB_flag) {
            unset($DB);
        }

        if ($product_id_list) {
            array_unique($product_id_u_list);
            return Array(
                'orders' => $return_data,
                'order_id_list' => $product_id_u_list
            );
        }

        return $return_data;

    }

    function itemPassedFlag($id_list) {
        $DB = DB_Connect();

        $inClause = substr(str_repeat(',?', count($id_list)), 1);
        $sql = "UPDATE
                    `T_ORDER_INFORMATION_DETAIL`
                SET
                    `passed_flag` = 1
                WHERE
                    `order_item_id` IN (%s)";
        $sql = $DB -> prepare(sprintf($sql, $inClause));
        return $sql -> execute($id_list);
    }