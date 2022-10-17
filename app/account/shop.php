<?php
    function getStore($DB) {
        $sql = "SELECT * FROM ORDER_SYS_DB.`T_STORE_INFORMATION` WHERE 1";

        $sql = $DB -> prepare($sql);
        $sql -> execute();
        return $sql -> fetchAll(PDO::FETCH_ASSOC);
    }

    function pushSet($data, $DB) {
        if ($_COOKIE['LOGIN_SESS']) {
            // データのアップデート
            setcookie('LOGIN_SESS', $_COOKIE['LOGIN_SESS'], time() + 60*60*7, '/', 'kousensai.apori.jp', true, true);

            $sql = "UPDATE
                        `T_NOTICE_DATA`
                    SET
                        `end_point`,
                        `public_key`,
                        `auth_token`
                    WHERE
                        `session_token` = :session_token";
            $sql = $DB -> prepare($sql);
            $sql -> bindValue(':session_token', $_COOKIE['LOGIN_SESS'], PDO::PARAM_STR);
            $sql -> execute();
            return true;
        }
        // 新しいセッションの作成
        $new_id = session::newToken($DB);
        setcookie('LOGIN_SESS', $new_id, time() + 60*60*7, '/', 'kousensai.apori.jp', true, true);

        $sql = "INSERT INTO `T_NOTICE_DATA` (
                    `session_token`,
                    `end_point`,
                    `public_key`,
                    `auth_token`
                )
                VALUES (
                    :sess_token,
                    :end_point,
                    :public_key,
                    :auth_token
                );
                INSERT INTO `T_STORE_TERMINAL_SESSION` (
                    `session_token`,
                    `store_id`,
                    `deleted`
                )
                VALUES (
                    :sess_token,
                    :store_id,
                    0
                );";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':sess_token', $new_id, PDO::PARAM_STR);
        $sql -> bindValue(':end_point', $data['endpoint'], PDO::PARAM_STR);
        $sql -> bindValue(':public_key', $data['userPublicKey'], PDO::PARAM_STR);
        $sql -> bindValue(':auth_token', $data['userAuthToken'], PDO::PARAM_STR);
        $sql -> bindValue(':store_id', $_SESSION['store_id'], PDO::PARAM_STR);

        return $sql -> execute();
    }