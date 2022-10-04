<?php
    require_once('../path.php');

    preg_match('/^\/menu\/([\w\/]*)\/?/', $_SERVER['REQUEST_URI'], $matches);
    $request = explode('/', $matches[1]);

    $product_column_template = file_get_contents(ROOT_PATH.'\template\costomer\product_column.html');

    switch($request[0]) {
        // メニュー一覧
        case '':
            $menu_list = Array(
                Array(
                    'store_name' => 'ぱーらーしまりん',
                    'product_name' => '焼きそば',
                    'price' => 300,
                    'img' => NULL,
                    'product_id' => '12345'
                ),
                Array(
                    'store_name' => 'ぱーらーしまりん',
                    'product_name' => 'ホットドック',
                    'price' => 200,
                    'img' => NULL,
                    'product_id' => '12346'
                ),
                Array(
                    'store_name' => 'ぱーらーしまりん',
                    'product_name' => 'しゅわとろサイダー',
                    'price' => 150,
                    'img' => NULL,
                    'product_id' => '12347'
                ),
                Array(
                    'store_name' => 'ぱーらーしまりん',
                    'product_name' => 'トロピカルサイダー',
                    'price' => 150,
                    'img' => NULL,
                    'product_id' => '12348'
                ),
                Array(
                    'store_name' => 'ぱーらーしまりん',
                    'product_name' => 'さんぴん茶',
                    'price' => 100,
                    'img' => NULL,
                    'product_id' => '12349'
                ),
            );

            //
            $menu_list_html = '';
            foreach($menu_list as $val) {
                $menu_list_html .= productColumn($val).PHP_EOL;
            }

            require_once(ROOT_PATH . '\template\costomer\menu.html');
            break;
        case 'product':
            try {
                $product_id = $request[1];
                echo $product_id;
            }catch (Exception $e) {
                // 404
            }
            break;
    }

    function productColumn($data) {
        global $product_column_template;

        $product_id = $data['product_id'];

        // データの置き換え
        $url = "https://localhost/menu/product/${product_id}/";
        $column_html = str_replace('{product_url}', $url, $product_column_template);

        $img = ($data['img'] == NULL)? 'No Image': '<img src="'.$data['img'].'">';
        $column_html = str_replace('{product_img}', $img, $column_html);

        $column_html = str_replace('{store_name}', $data['store_name'], $column_html);
        $column_html = str_replace('{product_name}', $data['product_name'], $column_html);
        $column_html = str_replace('{price}', $data['price'], $column_html);

        return $column_html;
    }