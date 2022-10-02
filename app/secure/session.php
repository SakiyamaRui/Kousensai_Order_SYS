<?php

    //SESSIONの自動延長
    if ( isset($_COOKIE[SESSION_CONFIG['SESSION_COOKIE_NAME']]) ) {

        $SESSION_ID = $_COOKIE['SID'];  //現在のsession_idを取得

        setcookie(
            SESSION_CONFIG['SESSION_COOKIE_NAME'],
            $SESSION_ID,
            time() + SESSION_CONFIG['SESSION_LIFETIME'],//有効期限
            '/',                                        //有効範囲のパス
            $_SERVER['SERVER_NAME'],                    //sessionのドメイン
            SESSION_CONFIG['SESSION_COOKIE_SECURE'],    //JavaScriptからの取得を無効に
            SESSION_CONFIG['SESSION_COOKIE_HTTP_ONLY']  //httpsのみ
        );

    }

    class session {
        /**
         * function start
         * 
         * sessionが開始されていない場合は開始する
         */
        static function start() {
            if (!self::isSESSION()) {
                session_start();
            }

            return true;
        }

        /**
         * function isSESSION
         * 
         * sessionが開始されているか確認する
         * 開始されている場合はtrue,
         * 開始されていない場合はfalseを返す
         */
        static function isSESSION() {
            if (session_status() == PHP_SESSION_NONE) {
                //sessionが開始されていない

                return false;

            }else{
                //sessionが開始されている

                return true;
            }
        }

    }