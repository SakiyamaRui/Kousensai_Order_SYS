<?php
    function orderReserve($option, $fingerPrint = '') {

        // tokenIDの取得
        $token_id = session::token_start();

        if ($token_id == false) {
            // トークンの復元に失敗
            return Array('result' => false, 'type' => 'token-error');
        }else{
            $DB = DB_Connect();
            getCartData($DB, $token_id);

            if (!isset($_SESSION['cart'])) {
                // カートが空
                return 'no-content';
            }
            if (count($_SESSION['cart']) == 0) {
                return 'no-content';
            }
        }

        $product_list = array_unique(array_column($_SESSION['cart'], 'product_id'));

        // 予約確認
        $result = stockCheck($_SESSION['cart'], false, $DB);

        if ($result !== true) {
            return Array('result' => false, 'type' => 'stock', 'detail' => $result);
        }

        // フィンガープリントのアップデート
        if ($fingerPrint != '') {
            session::fingerPrintUpdate($token_id, $fingerPrint, $DB);
        }

        // テーブルに追加
        $order_request_list = $_SESSION['cart'];

        // 値段一覧を取得
        /**
         * ・オプションと商品の単価を入れる
         */
        $price = getPriceList($DB, $product_list);
        $option_price = getOptionData($product_list, 0);
        
        $id = newOrderId($DB, $option);
        $placeholder = Array();
        for ( $i = 0; $i < count($order_request_list); $i++) { 
            $placeholder[] = "(:order_id, :product_id${i}, :quantity${i}, :product_option${i}, :unit_price${i})";
        }

        $sql = "INSERT INTO
                `T_ORDER_INFORMATION_DETAIL`
                (
                    `order_id`,
                    `product_id`,
                    `quantity`,
                    `product_option`,
                    `unit_price`
                )
                VALUES ". join(", ", $placeholder);
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(":order_id", $id['order_id'], PDO::PARAM_STR);
        foreach ($order_request_list as $key => $val) {
            $sql -> bindValue(":product_id${key}", $val['product_id'], PDO::PARAM_STR);
            $sql -> bindValue(":quantity${key}", $val['quantity'], PDO::PARAM_INT);
            $sql -> bindValue(":product_option${key}", json_encode($val['options'], JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);
            $sql -> bindValue(":unit_price${key}", $price[$val['product_id']]['product_price'], PDO::PARAM_INT);
        }

        try {
            $sql -> execute();
        }catch (PDOException $e) {
            echo $e;
            $DB -> rollback();
        }
        // 合計の値段を取得
        $total = 0;
        foreach ($order_request_list as $value) {
            $sum = $price[$value['product_id']]['product_price'];

            // オプションの値段を取得
            foreach ($value['options'] as $key => $option) {
                if (isset($option_price[$value['product_id']])) {
                    $array = array_column(array_column(array_values($option_price[$value['product_id']]), NULL, 'name')[$key]['option_values'], NULL, 'value');
                    $sum += $array[$option]['price'];
                }
            }

            $total += ($sum * $value['quantity']);
        }

        $sql = "UPDATE
                    `T_ORDER_INFORMATION_MAIN`
                SET
                    `order_total_price` = :total,
                    `session_token` = :session_token
                WHERE
                    `token` = :token";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':total', $total, PDO::PARAM_INT);
        $sql -> bindValue(':session_token', $_SESSION['token'], PDO::PARAM_STR);
        $sql -> bindValue(':token', $id['token'], PDO::PARAM_STR);
        try {
            $result = $sql ->execute();
        }catch (PDOException $e) {
            echo $e;
            $DB -> rollback();

            return false;
        }

        $DB -> commit();

        unset($DB);
        if ($result == true) {
            reset_cart();
            return Array(
                'result' => true,
                'type' => 'order-correct',
                'detail' => Array(
                    'token' => $id['token'],
                    'order_id' => $id['order_id']
                )
            );
        }
    }

    function appendOrder($cart) {
        $DB = DB_Connect();
    }

    // オプションのアップデート
    function updateOption($unique_id, $option_json) {
        $DB = DB_Connect();

        $sql = "UPDATE
                    `T_ORDER_INFORMATION_DETAIL`
                SET
                    `product_option` = :product_option
                WHERE
                    `order_item_id` = :order_item_id";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':order_item_id', $unique_id, PDO::PARAM_STR);
        $sql -> bindValue(':product_option', $option_json, PDO::PARAM_STR);

        return $sql -> execute();
    }

    function newOrderId($DB, $option) {
        // 注文番号の生成
        while (1) {
            $newOrderId = generateRandomNumberID(5);
            $newToken = generateToken(20);
            $sql = "INSERT INTO
                    `T_ORDER_INFORMATION_MAIN` (
                        `token`,
                        `order_id`,
                        `pickup_now`,
                        `pickup_time`
                    )
                    VALUES (
                        :newToken,
                        :newOrderId,
                        :pickUpType,
                        :pickUpTime
                    );";
            $sql = $DB -> prepare($sql);
            $sql -> bindValue(':newToken', $newToken, PDO::PARAM_STR);
            $sql -> bindValue(':newOrderId', $newOrderId, PDO::PARAM_STR);
            $sql -> bindValue(':pickUpType', $option['pickup_now'], PDO::PARAM_BOOL);
            $sql -> bindValue(':pickUpTime', (isset($option['pickup_time']))?$option['pickup_time']: '00:00', PDO::PARAM_STR);
            $result = $sql -> execute();

            if ($result == true) {
                return Array('token' => $newToken, 'order_id' => $newOrderId);
            }
        }
    }

    function orderConfirm($order_id, $DB = null) {
        $DB_Flag = false;
        if ($DB == null) {
            $DB = DB_Connect();
            $DB_Flag = true;
        }

        // 注文した商品一覧を取得
        $sql = "SELECT
                    `order_item_id`,
                    `product_id`,
                    `quantity`,
                    `product_option`,
                    `unit_price`
                FROM
                    `T_ORDER_INFORMATION_DETAIL`
                WHERE
                    `order_id` = :order_id";
        $sql = $DB -> prepare($sql);

        $sql -> bindValue(':order_id', $order_id, PDO::PARAM_STR);
        $sql -> execute();
        $order_items = $sql -> fetchAll();

        if ($order_items == false) {
            return Array('result' => false, 'type' => 'get_item');
        }

        // カートと同じ形式に治す
        $order_item_f_cart = Array();
        foreach($order_items as $val) {
            $column = Array(
                'product_id' => $val['product_id'],
                'quantity' => $val['quantity'],
                'options' => json_decode($val['product_option'], true)
            );
            array_push($order_item_f_cart, $column);
        }

        // 在庫確認
        $stock_result = stockCheck($order_item_f_cart, true, $DB);

        if ($stock_result !== true) {
            $DB -> rollback();
            return Array('result' => false, 'type' => 'stock_error', 'data' => $stock_result);
        }

        // 予約の確定処理
        $sql = "UPDATE
                    `T_ORDER_INFORMATION_MAIN`
                SET
                    `confirmed_order_flag` = 1
                WHERE
                    `order_id` = :order_id";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':order_id', $order_id, PDO::PARAM_STR);
        $sql -> execute();
        
        // 店舗側へ通知の送信

        return Array('result' => true);
    }
