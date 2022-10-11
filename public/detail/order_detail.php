<?php
    require_once(dirname(__DIR__).'/path.php');

    preg_match('/^\/detail\/([\w\/]*)\/?/', $_SERVER['REQUEST_URI'], $matches);
    $request = explode('/', $matches[1]);

    $token = $request[0];
    $order_id = '';

    //
    $DB = DB_Connect();

    $sql = "SELECT
                `order_id`,
                `order_total_price`,
                `confirmed_order_flag`,
                `session_token`,
                `pickup_now`,
                `pickup_time`
            FROM
                ORDER_SYS_DB.`T_ORDER_INFORMATION_MAIN`
            WHERE
                `token` = :token";
    $sql = $DB -> prepare($sql);

    $sql -> bindValue(':token', $token, PDO::PARAM_STR);
    $sql -> execute();
    $record = $sql -> fetch(PDO::FETCH_ASSOC);

    if ($record == false) {
        // 404
    }

    // 注文番号
    $order_id = $record['order_id'];

    // ステータス
    if ($record['confirmed_order_flag'] == 0) {
        $status = '支払い待ち';
        $status_body = '支払いが完了するまで注文が確定しません。<div>支払いを完了させてください。</div>';
    }else{
        $order_status = orderStatus($order_id, $DB);
        $status = $order_status['status'];

        switch ($status) {
            case '受け取り済み':
                $status_body = 'すべての商品の受け取りが完了しています。';
                break;
            case '受取可能':
                $status_body = 'すべての商品が完成しました。<div>各店舗で受け取りを行ってください。</div>';
                break;
            case '一部受取可能':
                $status_body = '一部の店舗ではすでに商品が完成しました。';
                break;
            case '注文確定済み':
                $status_body = 'お支払いが完了して注文が確定されました。<div>商品の完成までしばらくお待ち下さい。</div>';
                break;
        }
    }

    // 受け取り希望時間
    if ($record['pickup_now'] == 0) {
        $pickup_time = $record['pickup_time'];
    }else{
        $pickup_time = '今すぐ';
    }

    // 通知の受け取り設定
    $notice_bool = isNoticeSetting($record['session_token'], $DB);
    $notice_setting = ($notice_bool)?
        '<div class="notice-request"><div class="status">設定済み</div><button id="notice-request-button">端末の変更</button></div>':
        '<div class="notice-request"><div class="status">未設定</div><button id="notice-request-button">通知の設定</button></div>';
    //

    // 注文の詳細
    $option_template = file_get_contents(ROOT_PATH.'\template\costomer\order-detail-item-column-option.html');
    $column_template = file_get_contents(ROOT_PATH.'\template\costomer\order-detail-item-column.html');

    $order_item = orderItems($order_id, $DB);
    $item_list = Array();

    foreach($order_item as $val) {
        $column = str_replace('{product_name}', $val['product_name'], $column_template);
        
        // オプション
        $option_dom_list = Array();
        $option_json = json_decode($val['product_option'], true);
        foreach($option_json as $option_name => $option_value) {
            $option_column = str_replace('{option_name}', $option_name, $option_template);
            $option_column = str_replace('{option_value}', $option_value, $option_column);

            array_push($option_dom_list, $option_column);
        }

        if (count($option_json) > 0) {
            $column = str_replace('{options}', implode(PHP_EOL, $option_dom_list), $column);
        }else{
            $column = str_replace('{options}', '', $column);
        }

        array_push($item_list, $column);
    }

    $order_items = implode(PHP_EOL, $item_list);


    // 合計金額
    $total = $record['order_total_price'];

    require_once(ROOT_PATH.'\template\costomer\order-detail.html');