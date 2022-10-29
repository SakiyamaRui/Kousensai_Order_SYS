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
            $sql = "SELECT `confirmed_order_flag` FROM `T_ORDER_INFORMATION_MAIN` WHERE `canceled` = 0 AND ";
            if ($type == 'token') {
                $sql .= "`token` = :value";
            }else if ($type == 'order_id') {
                $sql .= "`order_id` = :value";
            }

            $sql = $DB -> prepare($sql);
            $sql -> bindValue(':value', $id, PDO::PARAM_STR);
            $sql -> execute();
            $paid = $sql -> fetch(PDO::FETCH_ASSOC);

            if ($paid == false) {
                echo 'false';
                exit;
            }

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
            session::start();
            if($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['store_id'])){
                // 404
                exit;
            }

            $DB = DB_Connect();

            // 値段・受け取り時間を変更
            $price = $request['price'];
            $sql = "UPDATE
                        `T_ORDER_INFORMATION_MAIN`
                    SET
                        `order_total_price` = :order_total,
                        `pickup_time` = :request_time,
                        `amount_received` = :amount_received
                    WHERE
                        `order_id` = :order_id";
            $sql = $DB -> prepare($sql);
            $sql -> bindValue(':order_total', $price, PDO::PARAM_INT);
            $sql -> bindValue(':order_id', $request['order_id'], PDO::PARAM_STR);
            $sql -> bindValue(':request_time', $request['time'], PDO::PARAM_STR);
            $sql -> bindValue(':amount_received', $request['get'], PDO::PARAM_INT);
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
        // 在庫状況の取得
        case 'stock':
            // 在庫一覧を取得する
            session::start();
            $DB = DB_Connect();

            if (isset($_SESSION['store_id']) && !isset($_GET['product_id'])) {
                // 商品一覧を取得する
                switch ($_SESSION['store_id']) {
                    case '会計局':
                    case '会計':
                    case 'root':
                    case '管理':
                    case '管理者':
                        $sql = "SELECT
                                    `product_id`,
                                    `product_name`
                                FROM
                                    `T_PRODUCT_INFORMATION`
                                WHERE
                                    `orderable_flag` = 1";
                        $sql = $DB -> prepare($sql);
                        $sql -> execute();
                        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);
                        
                        $product_id_list = array_column($record, 'product_id');
                        break;
                    default:
                        $sql = "SELECT
                                    `product_id`,
                                    `product_name`
                                FROM
                                    `T_PRODUCT_INFORMATION`
                                WHERE
                                    `orderable_flag` = 1 AND
                                    `store_id` = :store_id";
                        $sql = $DB -> prepare($sql);
                        $sql -> bindValue(':store_id', $_SESSION['store_id'], PDO::PARAM_STR);
                        $sql -> execute();
                        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

                        $product_id_list = array_column($record, 'product_id');
                        break;
                }
            }else{
                if (!isset($_GET['product_id'])) {
                    $sql = "SELECT
                                `product_id`,
                                `product_name`
                            FROM
                                `T_PRODUCT_INFORMATION`
                            WHERE
                                `orderable_flag` = 1";
                    $sql = $DB -> prepare($sql);
                    $sql -> execute();
                }else{
                    $sql = "SELECT
                                `product_id`,
                                `product_name`
                            FROM
                                `T_PRODUCT_INFORMATION`
                            WHERE
                                `product_id` = :product_id";
                    $sql = $DB -> prepare($sql);
                    $sql -> bindValue(':product_id', $_GET['product_id'], PDO::PARAM_STR);
                    $sql -> execute();
                }

                $record = $sql -> fetchAll(PDO::FETCH_ASSOC);    
                $product_id_list = array_column($record, 'product_id');
            }

            // IDリストから在庫を取得
            $products_stock = getStock_data_product($product_id_list, false, $DB);
            $product_options = getOptionData($product_id_list);
            
            //オプションの情報
            $option_id_list = Array();
            $option_data_link = Array();
            // var_dump($product_options);

            // IDと参照データのフォーマット
            foreach ($product_options as $product_id => $val) {
                $option_data_link[$product_id] = Array();

                foreach ($val as $option_name => $opt_val) {
                    if (!isset($_SESSION['store_id']) && $opt_val['isPublic'] == false) {
                        continue;
                    }

                    $option_data_link[$product_id][$option_name] = Array();

                    foreach ($opt_val['option_values'] as $value) {
                        $option_data_link[$product_id][$option_name][$value['value']] = $value['option_id'];
                        array_push($option_id_list, $value['option_id']);
                    }
                }
            }

            $options_stock = getStock_data_option($option_id_list, false, $DB);
            // データの作成
            $return_data = Array();

            foreach($product_id_list as $product_id) {
                $return_data[$product_id] = Array();
                $p_data = &$return_data[$product_id];

                // 商品名の検索
                $product_index = array_search($product_id, $product_id_list);
                $p_data['product_name'] = $record[$product_index]['product_name'];

                // 商品の検索
                $p_i = array_search($product_id, array_column($products_stock, 'product_id'));
                if ($p_i !== false) {
                    $p_stock = $products_stock[$p_i]['current_stock'];

                    $p_data['stock'] = (int)$p_stock;
                }

                // オプション
                if (!array_key_exists($product_id, $option_data_link)) {
                    unset($p_data);
                    continue;
                }

                $p_data['option'] = Array();
                $sum = 0;
                
                // オプションのデータを取得
                foreach($option_data_link[$product_id] as $option_name => $option_v) {
                    $p_data['option'][$option_name] = Array();

                    foreach($option_v as $option_value => $option_id) {
                        $opt_i = array_search($option_id, array_column($options_stock, 'option_id'));
                        if ($opt_i === false) {
                            $p_data['option'][$option_name][$option_value] = Array('stock' => '--');
                            continue;
                        }

                        $p_data['option'][$option_name][$option_value] = Array(
                            'stock' => $options_stock[$opt_i]['current_stock'],
                            'option_id' => $option_id
                        );
                        $sum += ($options_stock[$opt_i]['current_stock'] == -1)? 0: $options_stock[$opt_i]['current_stock'];
                    }
                }

                // 一番要素が長いオプションを在庫数とする
                $best_key = '';
                $length = -1;
                foreach ($p_data['option'] as $option_name => $option_v) {
                    $len = count($option_v);

                    if ($length < $len) {
                        $length = $len;
                        $best_key = $option_name;
                        continue;
                    }
                }

                $sum = 0;
                foreach ($p_data['option'][$best_key] as $option_val => $option_id) {
                    $sum += ($option_id['stock'] == '--')? 0: $option_id['stock'];
                }

                if (array_key_exists('stock', $p_data)) {
                    if ($p_data['stock'] == -1) {
                        $p_data['stock'] = $sum;
                    }
                }else{
                    $p_data['stock'] = $sum;
                }

                unset($p_data);
            }

            echo json_encode($return_data);
            exit;
            break;
        case 'practice':
            echo 'Hello World!';
            break;
        // 
        case 'sales':
            $DB = DB_Connect();
            $data = salesCalculation($DB);
            header('Content-Type: application/json');
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            break;
        case 'calc':
            $DB = DB_Connect();
            $data = total_calc($DB);
            header('Content-Type: application/json');
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            break;
    }