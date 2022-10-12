<?php
    function noticeSubscribe($data) {
        // トークンの復元
        $token_id = session::token_start();

        if ($token_id == false) {
            return false;
        }

        $DB = DB_Connect();

        // トークンをアップデート
        $sql = "UPDATE
                    `T_ORDER_INFORMATION_MAIN`
                SET
                    `session_token` = :session_token
                WHERE
                    `token` = :token";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':session_token', $token_id, PDO::PARAM_STR);
        $sql -> bindValue(':token', $data['order_token'], PDO::PARAM_STR);

        $sql -> execute();

        // NOTICE_DATAをUPDATE
        $sql = "UPDATE
                    `T_NOTICE_DATA`
                SET
                    `end_point` = :end_point,
                    `public_key` = :public_key,
                    `auth_token` = :auth_token
                WHERE
                    `session_token` = :session_token";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':session_token', $token_id, PDO::PARAM_STR);
        $sql -> bindValue(':end_point', $data['endpoint'], PDO::PARAM_STR);
        $sql -> bindValue(':public_key', $data['userPublicKey'], PDO::PARAM_STR);
        $sql -> bindValue(':auth_token', $data['userAuthToken'], PDO::PARAM_STR);
        return $sql -> execute();
    }