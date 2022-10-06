<?php
require_once(dirname(__DIR__).'\main.php');

$return_template = Array(
    'store_name' => "",     // 店舗名
    'product_name' => "",   // 商品名
    'price' => 0,           // 値段
    'img' => "",            // 商品画像のURL
    'product_id' => ""      // 商品識別子
    'product_detail' => ""  //商品の説明
);

function getAllProductIndexList($id) {
    //
    global $return_template;
    $return_data = Array();

    //DB接続
    $DB = DB_Connect();
    $sql = "SELECT `product_name`, `store_id`, `product_price`, `product_image_url`, `product_id`, `product_detail` FROM ORDER_SYS_DB.`T_PRODUCT_INFORMATION` WHERE `product_id` = :product_id";
    $sql = $DB -> prepare($sql);
    $sql -> bindValue(':product_id', $id, PDO::PARAM_STR);
    $sql -> execute();
    $All_product_data = $sql -> fetchAll(PDO::FETCH_ASSOC);

    // テンプレートのコピー
    foreach ($All_product_data as $val) {
        $column = $return_template;

        // 置き換える
        $column['product_name'] = $val['product_name'];
        $column['price'] = $val['product_price'];
        $column['img'] = $val['product_image_url'];
        $column['product_id'] = $val['product_id'];
        $column['product_detail'] = $val['product_detail'];

        // フォーマットを置き換えたものを返す配列に追加
        array_push($return_data, $column);

        // $store_id_listの中にあるidのストア名を取得
    $inClause = substr(str_repeat(',?', count($store_id_list)), 1);
    $sql = "SELECT * FROM ORDER_SYS_DB.`T_STORE_INFORMATION` WHERE `store_id`IN(%s)";
    $sql = $DB -> prepare(sprintf($sql, $inClause));
    $sql -> execute($store_id_list);

    //store_idでデータを取得できるようにした
    $store_name_list = $sql -> fetchAll(PDO::FETCH_ASSOC);
    $store_name_list = array_column($store_name_list, NULL, 'store_id');
    
    //store_idをstore_nameに置き換えるループ
    foreach ($return_data as &$val) {
        $val['store_name'] = $store_name_list[$val['store_name']]['store_name'];
        unset($val);
    }

    return $return_data;
    }
}
