<?php
require_once(dirname(__DIR__).'/main.php');

$return_template = Array(
    'store_name' => "",     // 店舗名
    'product_name' => "",   // 商品名
    'price' => 0,           // 値段
    'img' => "",            // 商品画像のURL
    'product_id' => ""      // 商品識別子
);

function getAllProductIndexList() {
    //
    global $return_template;
    $return_data = Array();
    $store_id_list = Array();

    //DB接続
    $DB = DB_Connect();
    $sql = "SELECT
                `product_id`,
                `store_id`,
                `product_name`,
                `product_price`,
                `product_image_url`
            FROM
                `T_PRODUCT_INFORMATION`
            WHERE
                `orderable_flag` = 1
            ORDER BY `product_price` ASC";
    $sql = $DB -> prepare($sql);
    $sql -> execute();
    $All_product_data = $sql -> fetchAll(PDO::FETCH_ASSOC);

    // テンプレートのコピー
    foreach ($All_product_data as $val) {
        $column = $return_template;
        
        // 置き換える
        $column['store_name'] = $val['store_id'];
        $column['product_name'] = $val['product_name'];
        $column['price'] = $val['product_price'];
        $column['img'] = $val['product_image_url'];
        $column['product_id'] = $val['product_id'];

        // フォーマットを置き換えたものを返す配列に追加
        array_push($return_data, $column);
        
        // 重複を記録するためにプッシュ
        array_push($store_id_list, $val['store_id']);
    }

    // 重複を消す
    $store_id_list = array_unique($store_id_list);

    // $store_id_listの中にあるidのストア名を取得
    $inClause = substr(str_repeat(',?', count($store_id_list)), 1);
    $sql = "SELECT `store_id`, `store_name` FROM `T_STORE_INFORMATION` WHERE `store_id` IN(%s)";
    $sql = $DB -> prepare(sprintf($sql, $inClause));
    $sql -> execute(array_values($store_id_list));

    //store_idでデータを取得できるようにした
    $store_name_list = $sql -> fetchAll(PDO::FETCH_ASSOC);
    $store_name_list = array_column($store_name_list, NULL, 'store_id');
    
    //store_idをstore_nameに置き換えるループ
    foreach ($return_data as &$val) {
        $val['store_name'] = $store_name_list[$val['store_name']]['store_name'];
        unset($val);
    }

    unset($DB);
    return $return_data;
}


// Optionデータの取得
function getOptionData($id_list, $type = 0, $DB = null) {
    /**
     * $type = 0: 在庫状況なし
     * $type = 1: 在庫状況も取得
     */
    // DBへ接続
    $DB_Flag = false;
    if ($DB == null) {
        $DB = DB_Connect();
        $DB_Flag = true;
    }

    // 配列ではない場合と配列の場合でsql実行を変える
    if (!is_array($id_list)) {
        // 配列でない場合
        $sql = "SELECT * FROM
                    `T_PRODUCT_OPTIONS`
                WHERE
                    `product_id` = :product_id";
        $sql = $DB -> prepare($sql);

        $sql -> bindValue(':product_id', $id_list, PDO::PARAM_STR);
        $sql -> execute();
        $data = $sql -> fetchAll(PDO::FETCH_ASSOC);
    }else{
        // 配列の場合
        $inClause = substr(str_repeat(',?', count($id_list)), 1);
        if (count($id_list) == 0) {
            return Array();
        }
        $sql = "SELECT
                    *
                FROM
                    `T_PRODUCT_OPTIONS`
                WHERE
                    `product_id` IN(%s)";
        $sql = $DB -> prepare(sprintf($sql, $inClause));

        $sql -> execute(array_values($id_list));
        $data = $sql -> fetchAll(PDO::FETCH_ASSOC);
    }

    // フォーマットに変換する
    $return_array = Array();
    foreach($data as $val) {
        if (!isset($val['product_id'])) {
            $key = $val[0]['product_id'];
            $val = $val[0];
        }else{
            $key = $val['product_id'];
        }

        if (!isset($return_array[$key])) {
            $return_array[$key] = Array();
        }
        $column = &$return_array[$key];

        if (!isset($column[$val['option_name']])) $column[$val['option_name']] = Array(
            'name' => $val['option_name'],
            'isPublic' => ($val['user_display_flag'] == 1)? true: false,
            'option_values' => Array(),
            'default' => ''
        );

        $append = Array(
            'value' => $val['option_value'],
            'price' => $val['add_option_price'],
            'index' => $val['option_index'],
            'option_id' => $val['option_id']
        );

        array_push($column[$val['option_name']]['option_values'], $append);

        if ($column[$val['option_name']]['name'] == '') {
            $column[$val['option_name']]['name'] == $val['option_name'];
        }

        if ($column[$val['option_name']]['isPublic'] == '') {
            $column[$val['option_name']]['isPublic'] == ($val['user_display_flag'] == 1)? true: false;
        }

        if ($val['default_value'] == 1) {
            $column[$val['option_name']]['default'] = $val['option_value'];
        }

        unset($column);
    }

    if ($DB_Flag) {
        unset($DB);
    }
    return $return_array;
}

function getOptionDataRelease($id) {
    $data = getOptionData($id);

    if (isset($data[$id])) {
        return array_values($data[$id]);
    }else{
        return Array();
    }
}

function getProductData($id_list, $DB = false) {
    if ($DB == false) {
        $DB = DB_Connect();
    }

    $inClause = substr(str_repeat(',?', count($id_list)), 1);
    $sql = "SELECT
                `product_id`,
                `store_id`,
                `product_name`,
                `product_price`
            FROM
                `T_PRODUCT_INFORMATION`
            WHERE
                `product_id` IN(%s)";
    $sql = $DB -> prepare(sprintf($sql, $inClause));
    $sql -> execute(array_values($id_list));
    $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

    //
    $list = Array();
    foreach ($record as $row) {
        $list[$row['product_id']] = Array(
            'shop_name' => $row['store_id'],
            'product_name' => $row['product_name'],
            'price' => $row['product_price'],
            'options' => Array()
        );
    }

    // オプション・店舗名を取得
    $store_id_list = array_column($record, 'store_id');

    // 店舗名
    $inClause = substr(str_repeat(',?', count($store_id_list)), 1);
    $sql = "SELECT * FROM `T_STORE_INFORMATION` WHERE `store_id` IN(%s)";
    $sql = $DB -> prepare(sprintf($sql, $inClause));
    $sql -> execute($store_id_list);
    $store_name_list = array_column($sql -> fetchAll(PDO::FETCH_ASSOC), NULL, 'store_id');

    // オプション
    $options = getOptionData($id_list);

    // 置き換える
    foreach($list as $key => &$column) {
        if (isset($options[$key])) {
            $column['options'] = array_values($options[$key]);
        }else{
            $column['options'] = Array();
        }
        $column['shop_name'] = $store_name_list[$column['shop_name']]['store_name'];
    }

    return $list;
}

function getProductName($id_list, $DB) {
    $inClause = substr(str_repeat(',?', count($id_list)), 1);
    $sql = "SELECT
                `product_id`,
                `product_name`
            FROM
                `T_PRODUCT_INFORMATION`
            WHERE
                `product_id` IN(%s)";
    $sql = $DB -> prepare(sprintf($sql, $inClause));
    $sql -> execute(array_values($id_list));
    $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

    $return_array = Array();
    foreach ($record as $val) {
        $return_array[$val['product_id']] = $val['product_name'];
    }

    return $return_array;
}

function getPriceList($DB, $id_list) {
    $inClause = substr(str_repeat(',?', count($id_list)), 1);
    $sql = "SELECT `product_id`, `product_price` FROM `T_PRODUCT_INFORMATION` WHERE `product_id` IN(%s)";
    $sql = $DB -> prepare(sprintf($sql, $inClause));
    $sql -> execute(array_values($id_list));
    return array_column($sql -> fetchAll(PDO::FETCH_ASSOC), NULL, 'product_id');
}

