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

    /**
     * セッションの設定
     */
    session_save_path(SERVER_CONFIG['session_save']);   //sessionの保存先の変更
    ini_set('session.gc_maxlifetime', SESSION_CONFIG['SESSION_LIFETIME']);          //セッションの有効期限
    ini_set('session.cookie_lifetime', SESSION_CONFIG['SESSION_LIFETIME']);         //セッションクッキーの有効期限
    ini_set('session.cookie_path', '/');                                            //セッションの有効パス
    ini_set('session.cookie_domain', DOMAIN);                                       //セッションを保存するドメイン
    ini_set('session.cookie_httponly', SESSION_CONFIG['SESSION_COOKIE_HTTP_ONLY']); //セッションをJSからアクセスできないように
    ini_set('session.cookie_secure', SESSION_CONFIG['SESSION_COOKIE_SECURE']);      //セッションクッキーをセキュア通信のみで使用
    ini_set('session.sid_length', SESSION_CONFIG['SESSION_ID_LENGTH']);             //セッションIDの長さ
    session_name(SESSION_CONFIG['SESSION_COOKIE_NAME']);                            //セッションクッキー名
