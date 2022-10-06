<?php

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $request = file_get_contents('php://input');
    }

    switch ($_GET['mode']) {
        // 商品の詳細情報の取得
        case 'getProductDetail':
            break;
        // カート情報の取得
        case 'getCartData':
            break;
        // カート情報の変更
        case 'changeCartData':
            break;
        case 'cartAppend':
            if (!isset($_POST['order_data'])) {
                // 403エラー
            }

            // 追加する関数の呼び出し
            $result = add_cart($_POST['order_data']);

            if ($result == false) {
                // エラーを出力
            }else{
                echo 'true';
            }
            break;
        // webPush通知の登録
        case 'notice-subscription':
            break;
    }