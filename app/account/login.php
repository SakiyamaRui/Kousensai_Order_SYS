<?php
    require_once('C:\Users\ic211216\Desktop\創造研究\Kousensai_Order_SYS\app\main.php');

    /**
     * Login
     * @param string $username
     * @param string $password
     */
    function storeLogin($username, $password) {
        $DB = $DB = DB_Connect();

        $sql = "SELECT * FROM ORDER_SYS_DB.`T_STORE_ACCOUNT` WHERE `account_name` = :account_name;";

        $sql = $DB -> prepare($sql);

        $sql -> bindValue(':account_name', $username, PDO::PARAM_STR);

        try {
            $sql -> execute();
            $result = $sql -> fetch();

            if ($result == false) {
                return false;
            }
        }catch (PDOExceptison $ex) {
            //DBエラー
            echo $ex;

            //処理

            return false;
        }

        $passCheck = password_verify($password, $result['account_pass']);

        if ($passCheck == false) {
            return false;
        }

        return Array(
            'store_id' => $result['store_account_id'],
            'account_name' => $result['account_name'],
        );
    }

    //function storeLogout() {}
