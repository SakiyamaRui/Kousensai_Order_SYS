<?php
    require_once(dirname(__DIR__).'/path.php');

    // カート情報の取得
    $token_id = session::token_start();
    if ($token_id == false) {
        $cart_data = Array();
    }else{
        $DB = DB_Connect();
        getCartData($DB, $token_id);

        if (!isset($_SESSION['cart'])) {
            $cart_data = Array();
        }

        // カートの中にある商品IDをリスト化
        if (isset($_SESSION['cart'])) {
            $product_id_list = array_unique(array_column($_SESSION['cart'], 'product_id'));
        }else{
            $product_id_list = Array();
        }

        if (count($product_id_list) != 0) {
            // 商品情報を取得する
            $product_list = getProductData($product_id_list, $DB);

            // フォーマット化
            $cart_data = $_SESSION['cart'];
            foreach ($cart_data as &$val) {
                $val['product_data'] = $product_list[$val['product_id']];
            }
        }else{
            $cart_data = [];
        }
    }

    require_once(ROOT_PATH.'/template/costomer/cart.html');