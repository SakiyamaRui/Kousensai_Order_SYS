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
            break;
        // webPush通知の登録
        case 'notice-subscription':
            break;
        case 'practice':
            echo 'Hello World!';
            break;
    }