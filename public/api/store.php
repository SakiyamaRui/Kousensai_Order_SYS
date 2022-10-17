<?php
    require_once(dirname(__DIR__).'/path.php');
    //
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $request = json_decode(file_get_contents('php://input'), true);
    }

    switch ($_GET['mode']) {
        // ログインリクエスト
        case 'login-request':
            session::start();
            if($_SERVER["REQUEST_METHOD"] != "POST"){
                // 404
                exit;
            }

            // ログインリクエストを行う
            $result = storeLogin($request['user'], $request['password']);

            if ($result == false) {
                echo 'false';
                exit;
            }

            if (isset($_SESSION['return_to'])) {
                $redirect = $_SESSION['return_to'];
            }else{
                $redirect = 'https://'.DOMAIN.'/manage/';
            }

            $_SESSION['store_id'] = $result['store_id'];
            $_SESSION['account_name'] = $result['account_name'];

            echo json_encode(Array('redirect' => $redirect));
            exit;
            break;

        // 注文情報の編集
        case 'getProductDetail':
            // 注文情報の取得
            if (isset($_GET['token'])) {
                $type = 'token';
                $id = $_GET['token'];
            }

            if (isset($_GET['order_id'])) {
                $type = 'order_id';
                $id = $_GET['order_id'];
            }

            $order_items = orderDetailShop($type, $id, null, false, true);

            echo json_encode($order_items, JSON_UNESCAPED_UNICODE);
            exit;
            break;
        // 支払い用のデータの取得
        case 'getPaymentOrder':
            // 注文情報の取得
            if (isset($_GET['token'])) {
                $type = 'token';
                $id = $_GET['token'];
            }
            else if (isset($_GET['order_id'])) {
                $type = 'order_id';
                $id = $_GET['order_id'];
            }else{
                // 404
                exit;
            }

            $DB = DB_Connect();

            // 支払いの確認
            $sql = "SELECT `confirmed_order_flag` FROM `T_ORDER_INFORMATION_MAIN` WHERE ";
            if ($type == 'token') {
                $sql .= "`token` = :value";
            }else if ($type == 'order_id') {
                $sql .= "`order_id` = :value";
            }

            $sql = $DB -> prepare($sql);
            $sql -> bindValue(':value', $id, PDO::PARAM_STR);
            $sql -> execute();
            $paid = $sql -> fetch(PDO::FETCH_ASSOC);
            if ($paid['confirmed_order_flag'] == 1) {
                echo json_encode(Array(
                    'payment' => true,
                    'order_id' => $id
                ));
                exit;
            }

            $order_items = orderDetailShop($type, $id, $DB, true);
            $product_data = getProductData($order_items['order_id_list'], $DB);

            // order_idの取得
            if ($type == 'token') {
                $sql = "SELECT `order_id` FROM `T_ORDER_INFORMATION_MAIN` WHERE `token` = :token";
                $sql = $DB -> prepare($sql);

                $sql -> bindValue(':token', $id, PDO::PARAM_STR);
                $sql -> execute();
                $id = $sql -> fetch()['order_id'];
            }

            try {
                echo json_encode(Array(
                    'order_data' => $order_items['orders'],
                    'product_data' => $product_data,
                    'payment' => false,
                    'order_id' => $id
                ));
            }catch(Exception $e) {
                echo 'false';
            }
            exit;
            break;
        // オプションのアップデート
        case 'changeOption':
            if($_SERVER["REQUEST_METHOD"] != "POST"){
                // 404
                exit;
            }

            try {
                echo json_encode(
                    updateOption($request['unique_id'], $request['options'])
                );
                exit;
            }catch(Exception $e) {
                echo 'false';
            }

            exit;
            break;
        // 受け渡し済み
        case 'passed':
            if($_SERVER["REQUEST_METHOD"] != "POST"){
                // 404
                exit;
            }
            if(itemPassedFlag($request)) {
                echo 'true';
            }else{
                echo 'false';
            }
            break;
        // 会計済み
        case 'paid':
            if($_SERVER["REQUEST_METHOD"] != "POST"){
                // 404
                exit;
            }

            $DB = DB_Connect();

            // 値段を変更
            $price = $request['price'];
            $sql = "UPDATE
                        `T_ORDER_INFORMATION_MAIN`
                    SET
                        `order_total_price` = :order_total
                    WHERE
                        `order_id` = :order_id";
            $sql = $DB -> prepare($sql);
            $sql -> bindValue(':order_total', $price, PDO::PARAM_INT);
            $sql -> bindValue(':order_id', $request['order_id'], PDO::PARAM_STR);
            $sql -> execute();

            // 在庫の確認
            $result = orderConfirm($request['order_id'], $DB);

            //
            if ($result['result'] ==  false) {
                echo json_encode($result);
                exit;
            }

            // 店舗端末へ通知
            pushOrder($request['order_id'], $DB);
            echo json_encode($result);
            exit;
        // 注文のアップデート
        case 'orderItemStatusUpdate':
            if($_SERVER["REQUEST_METHOD"] != "POST") {
                exit;
            }

            $DB = DB_Connect();
            try {
                // 注文をUPDATE
                updateOrderItemCreated($request, $DB);
                
                // UPDATEした商品の注文がすべて完了しているか確認
                $correct_list = array_keys($request, true);
                if (count($correct_list) < 1) {
                    echo 'true';
                    exit;
                }

                $inClause = substr(str_repeat(',?', count($correct_list)), 1);
                $sql = "SELECT
                            `order_id`,
                            `made_flag`
                        FROM
                            `T_ORDER_INFORMATION_DETAIL`
                        WHERE
                            `order_id` IN (
                                SELECT `order_id`
                                FROM `T_ORDER_INFORMATION_DETAIL`
                                WHERE `order_item_id` IN(%s)
                            )";
                $sql = $DB -> prepare(sprintf($sql, $inClause));
                $sql -> execute($correct_list);
                $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

                // 全件trueか確認
                $order_list = Array();
                foreach($record as $val) {
                    if (!isset($order_list[$val['order_id']])) {
                        $order_list[$val['order_id']] = true;
                    }

                    if ($val['made_flag'] == false) {
                        $order_list[$val['order_id']] = false;
                    }
                }
                

                // trueになっているオーダーは通知を送信
                $pushRequest_list = array_keys($order_list, true);
                if (count($pushRequest_list) == 0) {
                    echo 'true';
                    exit;
                }

                $result = pushAllCorrect($pushRequest_list, $DB);

                echo 'true';
            }catch(Exception $e) {
                echo 'false';
            }
            break;
        // webPush通知の登録
        case 'notice-subscription':
            if($_SERVER["REQUEST_METHOD"] != "POST") {
                exit;
            }

            autoLogin();

            $DB = DB_Connect();
            $result = pushSet($request, $DB);
            if ($result) {
                echo 'true';
            }else{
                echo 'false';
            }
            exit;
            break;
        case 'practice':
            echo 'Hello World!';
            break;
    }