<?php
    function isNoticeSetting($token_id, $DB) {
        $DB_flag = false;

        if ($DB == null) {
            $DB = DB_Connect();
            $DB_flag = true;
        }

        $sql = "SELECT
                    COUNT(*)
                FROM
                    `T_NOTICE_DATA`
                WHERE
                    `session_token` = :session_token AND
                    `end_point` IS NOT NULL AND
                    `public_key` IS NOT NULL AND
                    `auth_token` IS NOT NULL";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':session_token', $token_id, PDO::PARAM_STR);

        $sql -> execute();
        $record = $sql -> fetch(PDO::FETCH_ASSOC);

        if ($DB_flag) {
            unset($DB);
        }

        if ($record == false) {
            return false;
        }

        if ($record['COUNT(*)'] == 0) {
            return false;
        }else{
            return true;
        }
    }