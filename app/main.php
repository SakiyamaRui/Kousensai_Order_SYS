<?php
    /**
     * PHPの設定
     */
    date_default_timezone_set('Asia/Tokyo');    //タイムゾーンを Asia/Tokyoに設定
    mb_internal_encoding("utf-8");              // PHPの文字コードをUTF-8に
    mb_language("Japanese");                    // 使用言語を日本語に


    /**
     * 定数の宣言
     */
    define('ROOT_PATH', dirname(__DIR__));
    define('DOMAIN', (isset($_SERVER['SERVER_NAME']))? $_SERVER['SERVER_NAME']: '');


    /**
     * configファイルの読み込み
     */
    define('SERVER_CONFIG', parse_ini_file(ROOT_PATH.'/config/config.ini', false));
    define('SESSION_CONFIG', parse_ini_file(ROOT_PATH.'/config/session.ini', false));
    define('DB_CONFIG', parse_ini_file(ROOT_PATH.'/config/DB.ini', false));

    // ファイルの読み込み
    require_once(ROOT_PATH.'/vendor/autoload.php');
    require_once(ROOT_PATH.'/app/DB/database.php');
    require_once(ROOT_PATH.'/app/account/shop.php');
    require_once(ROOT_PATH.'/app/account/login.php');
    require_once(ROOT_PATH.'/app/secure/token.php');
    require_once(ROOT_PATH.'/app/secure/session.php');
    require_once(ROOT_PATH.'/app/PRODUCT/getData.php');
    require_once(ROOT_PATH.'/app/PRODUCT/getinformation.php');
    require_once(ROOT_PATH.'/app/Order/Cart.php');
    require_once(ROOT_PATH.'/app/Order/order-request.php');
    require_once(ROOT_PATH.'/app/Order/pull-order.php');
    require_once(ROOT_PATH.'/app/Order/shop_order.php');
    require_once(ROOT_PATH.'/app/Order/operation.php');
    require_once(ROOT_PATH.'/app/PRODUCT/stock_check.php');
    require_once(ROOT_PATH.'/app/notice/notice-settings.php');
    require_once(ROOT_PATH.'/app/notice/notice-subscribe.php');
    require_once(ROOT_PATH.'/app/qr-code/qr-code.php');
    require_once(ROOT_PATH.'/app/Push/web-push.php');
    require_once(ROOT_PATH.'/app/Push/customer_push.php');
    require_once(ROOT_PATH.'/app/server.php');

    // 公開・非公開モードの設定
    define('RUN_CONFIG', parse_ini_file(ROOT_PATH.'/config/status.ini', false));

    storeAutoLogin();

    if (RUN_CONFIG['runnning_mode'] == 'private') {
        // httpサーバーとして起動していない場合は無視
        if (!isset($_SERVER['SERVER_NAME'])) {
            goto private_skip;
        }

        // 管理者としてログインしている場合は起動を許容
        session::start();
        if (isset($_SESSION['store_id'])) {
            goto private_skip;
        }

        // 管理者ページへのアクセスは起動を許容
        preg_match('/\/(manage|api)/', $_SERVER['REQUEST_URI'], $matches, PREG_UNMATCHED_AS_NULL);
        if ($matches != null) {
            goto private_skip;
        }

        // 非公開モードの場合
        switch (RUN_CONFIG['private_mode']) {
            case 'before':
                require_once(ROOT_PATH .'/template/other/private_before.html');
                exit;
                break;
            case 'after':
                // トップページ、API、注文詳細のページは表示する
                preg_match('/\/(order|api|detail)/', $_SERVER['REQUEST_URI'], $matches, PREG_UNMATCHED_AS_NULL);
                
                if ($matches != null) {
                    goto private_skip;
                }

                require_once(ROOT_PATH .'/template/other/private_after.html');
                exit;
                break;
        }
    }

    private_skip:
