<?php
require_once(dirname(__DIR__).'\main.php');

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
    $sql = "SELECT `product_id`,`store_id`, `product_name`, `product_price`, `product_image_url` FROM ORDER_SYS_DB.`T_PRODUCT_INFORMATION` WHERE `orderable_flag` = 1 ORDER BY `product_price` ASC";
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

    unset($DB);
    return $return_data;
}


// Optionデータの取得
function getOptionData($id_list, $type = 0) {
    /**
     * $type = 0: 在庫状況なし
     * $type = 1: 在庫状況も取得
     */
    // DBへ接続
    $DB = DB_Connect();

    // 配列ではない場合と配列の場合でsql実行を変える
    if (!is_array($id_list)) {
        // 配列でない場合
        $sql = "SELECT * FROM
                    ORDER_SYS_DB.`T_PRODUCT_OPTIONS`
                WHERE
                    `product_id` = :product_id
                GROUP BY
                    `product_id`,
                    `option_name`,
                    `option_value`
                ";
        $sql = $DB -> prepare($sql);

        $sql -> bindValue(':product_id', $id_list, PDO::PARAM_STR);
        $sql -> execute();
        $data = $sql -> fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
    }else{
        // 配列の場合
        $inClause = substr(str_repeat(',?', count($id_list)), 1);
        $sql = "SELECT
                    *
                FROM
                    ORDER_SYS_DB.`T_PRODUCT_OPTIONS`
                WHERE
                    `product_id` IN(%s)
                GROUP BY
                    `product_id`,
                    `option_name`,
                    `option_value`";
        $sql = $DB -> prepare(sprintf($sql, $inClause));

        $sql -> execute($id_list);
        $data = $sql -> fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
    }

    // フォーマットに変換する
    $return_array = Array();
    foreach($data as $key => $val) {
        $return_array[$key] = Array();
        $column = &$return_array[$key];

        foreach($val as $option_select) {
            if ($option_select['option_name'] == '全体の在庫') {
                if ($type == 1) {
                    $column[$option_select['option_name']] = ($option_select['option_remaining_stock'] == -1)? INF: $option_select['option_remaining_stock'];
                }
                continue;
            }

            if (!isset($column[$option_select['option_name']])) $column[$option_select['option_name']] = Array('name' => '', 'isPublic' => '', 'option_values' => Array(), 'default' => '');

            $append = Array(
                'value' => $option_select['option_value'],
                'price' => $option_select['add_option_price'],
                'index' => $option_select['option_index']
            );
            if ($type == 1) {
                $append['stock'] = ($option_select['option_remaining_stock'] == -1)? INF: $option_select['option_remaining_stock'];
            }

            array_push($column[$option_select['option_name']]['option_values'], $append);

            if ($column[$option_select['option_name']]['name'] == '') {
                $column[$option_select['option_name']]['name'] = $option_select['option_name'];
            }

            if ($column[$option_select['option_name']]['isPublic'] == '') {
                $column[$option_select['option_name']]['isPublic'] = ($option_select['user_display_flag'] == 1)? true: false;
            }

            if ($option_select['default_value'] == 1) {
                $column[$option_select['option_name']]['default'] = $option_select['option_value'];
            }

        }

        unset($column);
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

function getProductData($id_list) {
    $DB = DB_Connect();

    $sql = "SELECT
                `product_id`,
                `store_id`,
                `product_name`,
                `product_price`
            FROM
                ORDER_SYS_DB.`T_PRODUCT_INFORMATION`
            WHERE
                `product_id` IN(%s);";
    $inClause = substr(str_repeat(',?', count($id_list)), 1);

    $sql = $DB -> prepare(sprintf($sql, $inClause));
    $sql -> execute($id_list);
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
    $sql = "SELECT * FROM ORDER_SYS_DB.`T_STORE_INFORMATION` WHERE `store_id`IN(%s)";
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

