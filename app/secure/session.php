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

        static function token_start() {
            self::start();

            if (!isset($_SESSION['token'])) {
                return $_SESSION['token'];
            }

            // Cookieの中から取得
            if (isset($_COOKIE['SESS_TOKEN'])) {
                $_SESSION['token'] = $_COOKIE['SESS_TOKEN'];
                return $_SESSION['token'];
            }

            // 転送してフィンガープリントから値を取得
            if (!isset($_POST['fingerprint'])) return null;
            if ($_POST['fingerprint'] != '') {
                $DB = DB_Connect();

                $sql = "SELECT
                            `session_token`
                        FROM
                            ORDER_SYS_DB.`T_NOTICE_DATA`
                        WHERE
                            `fingerprint` = :fingerprint";
                $sql = $DB -> prepare($sql);
                $sql -> bindValue(':fingerprint', $_POST['fingerprint'], PDO::PARAM_STR);

                $sql -> execute();
                $result = $sql -> fetch(PDO::FETCH_ASSOC);
                if ($result != false) {
                    $_SESSION['token'] = $result['session_token'];
                    return $_SESSION['token'];
                }
            }

            if ($result != false) {
                // 新しいセッションの開始
                $sql = "INSERT INTO
                            `T_NOTICE_DATA` (
                                `session_token`,
                                `fingerprint`
                            )
                        VALUES(
                            :new_token,
                            :fingerprint
                        );
                        INSERT INTO
                            `T_CART_DATA`(
                                `session_token`,
                                `product_in_cart`
                            )
                        VALUES (
                            :new_token,
                            '[]'
                        );";
                
                $new_id = session::newToken();
                $sql = $DB -> prepare($sql);
                $sql -> bindValue(':new_token', $new_id, PDO::PARAM_STR);
                $sql -> bindValue(':fingerprint', $_POST['fingerprint'], PDO::PARAM_STR);

                $result = $sql -> execute();

                if ($result == false) {
                    return false;
                }else{
                    $_SESSION['token'] = $result['session_token'];
                    return $_SESSION['token'];
                }
            }
        }

        static function newToken($DB) {
            while (1) {
                $new_id = generateToken($length);

                $sql = "SELECT * FROM ORDER_SYS_DB.`T_NOTICE_DATA` WHERE `session_token` = :session_token";
                $sql = $DB -> prepare($sql);

                $sql -> bindValue(':session_token', $new_id, PDO::PARAM_STR);
                $sql -> execute();

                $result = $sql -> fetch();

                if ($result == false) return $new_id;
            }
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