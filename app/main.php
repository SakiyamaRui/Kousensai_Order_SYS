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
    define('SERVER_CONFIG', parse_ini_file(ROOT_PATH.'\config\config.ini', false));
    define('SESSION_CONFIG', parse_ini_file(ROOT_PATH.'\config\session.ini', false));
    define('DB_CONFIG', parse_ini_file(ROOT_PATH.'\config\DB.ini', false));

    // ファイルの読み込み
    require_once(ROOT_PATH.'\app\DB\database.php');
    require_once(ROOT_PATH.'\app\secure\token.php');
    require_once(ROOT_PATH.'\app\secure\session.php');
    require_once(ROOT_PATH.'\app\account\login.php');
    require_once(ROOT_PATH.'\app\PRODUCT\getData.php');
    require_once(ROOT_PATH.'\app\PRODUCT\getinformation.php');
    require_once(ROOT_PATH.'\app\Order\Cart.php');
    require_once(ROOT_PATH.'\app\Order\order-request.php');
    require_once(ROOT_PATH.'\app\Order\pull-order.php');
    require_once(ROOT_PATH.'\app\PRODUCT\stock_check.php');
    // require_once(ROOT_PATH.'\app\server.php');

