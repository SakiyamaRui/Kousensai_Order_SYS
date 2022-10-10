<?php
    require_once(dirname(__DIR__).'/path.php');

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $request = json_decode(file_get_contents('php://input'), 1);
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
            if (!isset($request['data']) || isset($request['index'])) {
                // 403エラー
            }

            // 追加する関数の呼び出し
            $result = change_cart_data($request['index'], $request['data']);

            if ($result == false) {
                // エラーを出力
            }else{
                echo 'true';
            }
            break;
        case 'cartAppend':
            if (!isset($request['order_data'])) {
                // 403エラー
            }

            // 追加する関数の呼び出し
            $result = add_cart($request['order_data']);

            if ($result == false) {
                // エラーを出力
            }else{
                echo 'true';
            }
            break;
        case 'cartRemove':
            if (!isset($request['index'])) {
                // 403エラー
            }

            $result = remove_cart($request['index']);

            if ($result == false) {
                // エラーを出力
            }else{
                echo 'true';
            }
            break;
        case 'orderRequest':
            if($_SERVER["REQUEST_METHOD"] != "POST") {
                // 404
            }

            // 在庫確認・予約処理
            $result = orderReserve(
                Array(
                    'pickup_now' => $request['pickup_now'],
                    'pickup_time' => (isset($request['pickup_time']))? $request['pickup_time']: '00:00'
                ),
                $request['fingerPrint']
            );

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;

            break;
        case 'qr-code':
            require_once(ROOT_PATH.'\public\api\0.gif');
            break;
        // webPush通知の登録
        case 'notice-subscription':
            break;
        
        // デバック
        case 'debug':
            var_dump(orderReserve(Array('pickup_now' => true)));
            //
            break;

        default:
            // 404
            break;
    }