<?php
    // main.phpファイルを一度だけ読み込む
    require_once(dirname(__DIR__).'/main.php');

    // 関数定義
    function add_cart($data){                       //カートに商品を追加する関数
        $DB = DB_Connect();                         //DBと接続
        $token_id = session::token_start($DB);

        if ($token_id == false) return false;

        // セッション内にカート情報があるか確認
        if (!isset($_SESSION['cart'])) 
        {
            $result = getCartData($DB, $token_id);

            if ($result == false) return false;
        }
        
        //カートにデータ(商品)を追加
        array_push($_SESSION['cart'], $data); 

        $result = save_cart($DB, $token_id);                                                                                       
        unset($DB);                                                                                                    
        return $result;
    }

    function remove_cart($index)
    {
        $token_id = session::token_start();
        $DB = DB_Connect();

        if ($token_id == false) return false; 

        // セッション内にカート情報があるか確認
        if (!isset($_SESSION['cart'])) 
        {
            $result = getCartData($DB, $token_id);

            if ($result == false) return false;
        }

        array_splice($_SESSION['cart'], $index, 1);

        $result = save_cart($DB, $token_id);
        unset($DB);                                                                                                    
        return $result;
    }

    function reset_cart()
    {
        $token_id = session::token_start();
        $DB = DB_Connect();

        if ($token_id == false) return false; 

        // セッション内にカート情報があるか確認
        if (!isset($_SESSION['cart'])) 
        {
            $result = getCartData($DB, $token_id);

            if ($result == false) return false;
        }

        $_SESSION['cart'] = Array();

        $result = save_cart($DB, $token_id);
        unset($DB);                                                                                                    
        return $result;
    }

    function change_cart_data($index, $data)
    {
        $token_id = session::token_start();
        $DB = DB_Connect();

        if ($token_id == false) return false; 

        // セッション内にカート情報があるか確認
        if (!isset($_SESSION['cart'])) 
        {
            $result = getCartData($DB, $token_id);

            if ($result == false) return false;
        }

        $_SESSION['cart'][$index] = $data;
        $result = save_cart($DB, $token_id);
        unset($DB);                                                                                                    
        return $result;
    }

    // 関数定義
    function save_cart($DB, $token_id) {
        $json_data = json_encode($_SESSION['cart']);                                                                    //jsonを配列化
        
        $sql = "UPDATE `T_CART_DATA` SET `product_in_cart` = :json_data WHERE `session_token` = :session_token"; // カート情報をアップデート
        $sql = $DB -> prepare($sql);                                                                                    // sql文の実行準備
        $sql -> bindValue(':json_data', $json_data, PDO::PARAM_STR);                                                    // プレースホルダーの置き換え
        $sql -> bindValue(':session_token', $token_id, PDO::PARAM_STR);                                                 // プレースホルダーの置き換え
        $result = $sql -> execute();                                                                                    // sql文を実行出来たかどうかの確認 $resultに代入

        if ($result == false) return false;

        return true;

    }

    function getCartData($DB, $token_id)
    {
        $sql = "SELECT `product_in_cart` FROM `T_CART_DATA` WHERE `session_token` = :session_token";   // カート情報を取得
        $sql = $DB -> prepare($sql);                                                                                // sqlを実行準備する
        $sql -> bindValue(':session_token', $token_id, PDO::PARAM_STR);                                             // プレースホルダーの置き換え
        $sql -> execute();                                                                                          // sql文の実行
        $cart_data = $sql -> fetch();                                                                               // 実行結果を取得

        if ($cart_data == false) return false;

        $_SESSION['cart'] = json_decode($cart_data['product_in_cart'], true);                                                          // jsonを配列化
        
        return true;
    }