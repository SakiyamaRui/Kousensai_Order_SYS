<?php
    /**
     * @param string ユーザー名
     * @param string パスワード
     * @param string 店舗識別子
     * @return string 成功: アカウントID, 失敗: エラーメッセージ
     * 
     * 'NAME_USED' アカウント名がすでに使用されている
     * 'DB_ERROR' データベースエラーが発生した
     */
    function createStoreAccount($username, $password, $store_id) {
        // DB接続
        $DB = DB_Connect();

        // ユーザー名がすでに使われているかを確認する
        $sql = "SELECT `account_name` FROM ORDER_SYS_DB.`T_STORE_ACCOUNT` WHERE `account_name` = :account_name;";
        
        $sql = $DB -> prepare($sql);

        $sql -> bindValue(':account_name', $username, PDO::PARAM_STR);
        try {
            $sql -> execute();
            $result = $sql -> fetch();

            if ($result !== false) {
                return 'NAME_USED';
            }
        }catch (PDOExceptison $ex) {
            //DBエラー
            echo $ex;

            //処理

            return 'DB_ERROR';
        }

        //パスワードをハッシュ化
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // ユーザーを追加
        while (1) {
            $new_id = substr(bin2hex(random_bytes(10)), 0, 15);

            $sql = "INSERT INTO
                ORDER_SYS_DB.`T_STORE_ACCOUNT`
                SELECT
                :account_id,
                :account_name,
                :pass,
                :store_id
                WHERE
                NOT EXISTS (
                    SELECT
                    `store_account_id`
                    FROM
                    ORDER_SYS_DB.`T_STORE_ACCOUNT`
                    WHERE
                    `store_account_id` = :account_id
                );";

            $sql = $DB -> prepare($sql);

            $sql -> bindValue(':account_id', $new_id, PDO::PARAM_STR);
            $sql -> bindValue(':account_name', $username, PDO::PARAM_STR);
            $sql -> bindValue(':pass', $hashed, PDO::PARAM_STR);
            $sql -> bindValue(':store_id', $store_id, PDO::PARAM_STR);

            $sql -> execute();

            // 追加できているか確認
            $sql ="SELECT `store_account_id` FROM ORDER_SYS_DB.`T_STORE_ACCOUNT` WHERE `store_account_id` = :account_id AND `account_name` = :account_name;";
            $sql = $DB -> prepare($sql);

            $sql -> bindValue(':account_id', $new_id, PDO::PARAM_STR);
            $sql -> bindValue(':account_name', $username, PDO::PARAM_STR);
            $sql -> execute();

            if ($sql -> fetch() != false) {
                break;
            }
        }

        return $new_id;

        // DBとの接続を解除
        unset($DB);
    }