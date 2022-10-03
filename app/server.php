<?php

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