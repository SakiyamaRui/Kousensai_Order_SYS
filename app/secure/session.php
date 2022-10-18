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

        static function token_start($DB = null) {
            $DB_flag = false;
            self::start();
            $request = json_decode(file_get_contents('php://input'), 1);

            // Cookieの中から取得
            if (isset($_COOKIE['SESS_TOKEN'])) {
                $_SESSION['token'] = $_COOKIE['SESS_TOKEN'];
                return $_SESSION['token'];
            }

            // 転送してフィンガープリントから値を取得
            // if ($request != null) {
            //     if (!isset($request['fingerprint'])) return null;
            //     if ($request['fingerprint'] != '') {
            //         if ($DB == null) {
            //             $DB = DB_Connect();
            //             $DB_flag = true;
            //         }

            //         $sql = "SELECT
            //                     `session_token`
            //                 FROM
            //                     `T_NOTICE_DATA`
            //                 WHERE
            //                     `fingerprint` = :fingerprint";
            //         $sql = $DB -> prepare($sql);
            //         $sql -> bindValue(':fingerprint', $request['fingerprint'], PDO::PARAM_STR);

            //         $sql -> execute();
            //         $result = $sql -> fetch(PDO::FETCH_ASSOC);
            //         if ($result != false) {
            //             $_SESSION['token'] = $result['session_token'];
            //             setcookie('SESS_TOKEN', $_SESSION['token'], time() + 60 * 60 * 48, '/', DOMAIN, true, true);
                        
            //             if ($DB_flag) {
            //                 unset($DB);
            //             }

            //             return $_SESSION['token'];
            //         }
            //     }
            // }

            // 新しいセッションの開始
            if ($DB == null) {
                $DB = DB_Connect();
                $DB_flag = true;
            }

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

            $new_id = session::newToken($DB);
            $sql = $DB -> prepare($sql);
            $sql -> bindValue(':new_token', $new_id, PDO::PARAM_STR);
            $sql -> bindValue(':fingerprint', (isset($request['fingerprint']))? $request['fingerprint']: NULL, (isset($request['fingerprint']))? PDO::PARAM_STR: PDO::PARAM_NULL);

            $result = $sql -> execute();

            if ($result == false) {
                if ($DB_flag) {
                    unset($DB);
                }
                return false;
            }else{
                $_SESSION['token'] = $new_id;
                setcookie('SESS_TOKEN', $_SESSION['token'], time() + 60 * 60 * 48, '/', DOMAIN, true, true);

                if ($DB_flag) {
                    unset($DB);
                }
                return $_SESSION['token'];
            }

            return false;
        }

        static function fingerPrintUpdate($token_id, $fingerprint, $DB = null) {
            $DB_flag = false;

            if ($DB == null) {
                $DB = DB_Connect();
                $DB_flag = true;
            }

            $sql = "UPDATE
                        `T_NOTICE_DATA`
                    SET
                        `fingerprint` = :fingerprint
                    WHERE
                        `T_NOTICE_DATA`.`session_token` = :session_token";
            $sql = $DB -> prepare($sql);
            $sql -> bindValue(':session_token', $token_id, PDO::PARAM_STR);
            $sql -> bindValue(':fingerprint', $fingerprint, PDO::PARAM_STR);
            $sql -> execute();

            if ($DB_flag) {
                unset($DB);
            }
        }

        static function newToken($DB) {
            while (1) {
                $new_id = generateToken(50);

                $sql = "SELECT * FROM `T_NOTICE_DATA` WHERE `session_token` = :session_token";
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