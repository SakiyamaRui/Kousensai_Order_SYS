<?php
require_once(dirname(__DIR__).'\main.php');

$return_template = Array(
    'store_name' => "",     // 店舗名
    'product_name' => "",   // 商品名
    'price' => 0,           // 値段
    'img' => "",            // 商品画像のURL
    'product_id' => "",     // 商品識別子
    'product_detail' => ""  //商品の説明
);

function getProductDetail($id) {
    //
    global $return_template;
    $return_data = Array();

    //DB接続
    $DB = DB_Connect();
    $sql = "SELECT `product_id`, `store_id`, `product_name`, `product_detail`, `product_price`, `product_image_url` FROM ORDER_SYS_DB.`T_PRODUCT_INFORMATION` WHERE `product_id` = :product_id";
    $sql = $DB -> prepare($sql);
    $sql -> bindValue(':product_id', $id, PDO::PARAM_STR);
    $sql -> execute();
    $product_data = $sql -> fetch(PDO::FETCH_ASSOC);
    if ($product_data == false) {
        return false;
    }

    $return_data['product_id'] = $product_data['product_id'];
    $return_data['store_name'] = $product_data['store_id'];
    $return_data['product_name'] = $product_data['product_name'];
    $return_data['product_detail'] = $product_data['product_detail'];
    $return_data['price'] = $product_data['product_price'];
    $return_data['img'] = $product_data['product_image_url'];

    $sql = "SELECT * FROM ORDER_SYS_DB.`T_STORE_INFORMATION` WHERE `store_id` = :store_id";
    $sql = $DB -> prepare($sql);
    $sql -> bindValue(':store_id', $product_data['store_id'], PDO::PARAM_STR);
    $sql -> execute();
    
    $return_data['store_name'] = $sql -> fetch(PDO::FETCH_ASSOC)['store_name'];

    unset($sql);
    return $return_data;
}
