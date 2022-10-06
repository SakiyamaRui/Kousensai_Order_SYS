<?php
    //
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $request = file_get_contents('php://input');
    }

    switch ($_GET['mode']) {
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