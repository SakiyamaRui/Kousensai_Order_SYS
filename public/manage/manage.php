<?php
    require_once(dirname(__DIR__).'/path.php');

    preg_match('/^\/manage\/([\w\/]*)\/?/', $_SERVER['REQUEST_URI'], $matches);
    $request = explode('/', $matches[1]);

    switch ($request[0]) {
        case '':
            autoLogin();
            session::start();

            switch ($_SESSION['store_id']) {
                case '会計局':
                    require_once(ROOT_PATH.'/template/store/menu-payment.html');
                    break;

                //
                default:
                    require_once(ROOT_PATH.'/template/store/menu-store.html');
                    break;
            }
            exit;
            break;
        case 'login':
            require_once(ROOT_PATH .'/template/store/login.html');
            break;
        case 'logout':
            session::start();
            unset($_SESSION['store_id']);
            unset($_SESSION['account_id']);
            unset($_SESSION['account_name']);

            setcookie('LOGIN_SESS', '', 0, '/', DOMAIN, true, true);

            if (isset($_COOKIE['LOGIN_SESS'])) {
                $sql = "UPDATE
                        `T_STORE_TERMINAL_SESSION`
                    SET
                        `deleted` = 1
                    WHERE
                        `session_token` = :session_token";
                $DB = DB_Connect();
                $sql = $DB -> prepare($sql);

                $sql -> bindValue(':session_token', $_COOKIE['LOGIN_SESS'], PDO::PARAM_STR);
                $sql -> execute();
            }

            header('Location: https://kousensai.apori.jp/manage/');
            break;

        case 'item':
            $type = '登録';
            if ($request[1] == 'add') {

                // 店舗一覧の取得
                $DB = DB_Connect();
                
                $shop_list = getStore($DB);
                
                $template = '<option value="{store_id}">{store_name}</option>';
                $options = Array();
                $hide_shop = Array('会計局', 'root');
                foreach ($shop_list as $val) {

                    if (in_array($val['store_id'], $hide_shop)) {
                        continue;
                    }

                    $column = str_replace('{store_id}', $val['store_id'], $template);
                    $column = str_replace('{store_name}', $val['store_name'], $column);
                    array_push($options, $column);
                }

                $shop_options = implode(PHP_EOL, $options);
                
                require_once(ROOT_PATH.'/template/item/item-edit.html');
            }
            break;
        case 'orders':
            // ログインの確認
            autoLogin();
            session::start();

            $DB = DB_Connect();

            // 注文一覧を取得
            $orders = getOrders($_SESSION['store_id'], $DB);

            require_once(ROOT_PATH.'/template/store/order_list.html');
            break;
        case 'stock':
            // ログインの確認
            autoLogin();
            session::start();

            require_once(ROOT_PATH.'/template/store/stock_list.html');
            break;
        case 'reader':
            // ログイン確認
            autoLogin();
            if (!isset($_SESSION['store_id'])) {
                // 404
                exit;
            }
            switch ($_SESSION['store_id']) {
                case '会計局':
                case 'root':
                    // 会計局側
                    require_once(ROOT_PATH.'/template/store/payment-reader.html');
                    break;
                default:
                    require_once(ROOT_PATH.'/template/store/shop-reader.html');
                    break;
            }
            break;
    }