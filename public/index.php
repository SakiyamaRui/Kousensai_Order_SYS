<?php
    require_once(dirname(__DIR__).'/public/path.php');

    // DBへ接続
    $DB = DB_Connect();

    // IDを取得
    $token_id = session::token_start();

    // ユーザーの注文リストを取得
    $order_list = userOderList($token_id, $DB);

    if (count($order_list) == 0) {
        //
        $DOM = '注文はありません';
    }else{
        $template = file_get_contents(ROOT_PATH.'/template/costomer/top-order-list-temp.html');
        $list_columns = Array();

        foreach ($order_list as $val) {
            $column = str_replace('{url}', '/detail/'.$val['token'].'/', $template);
            $column = str_replace('{time}', date('H:i', strtotime($val['order_time'])), $column);
            $column = str_replace('{status}', $val['status'], $column);

            array_push($list_columns, $column);
        }

        $DOM = implode(PHP_EOL, $list_columns);
    }

    require_once(ROOT_PATH . '/template/costomer/top.html');