<?php
    require_once('C:\Users\ic211216\Desktop\創造研究\Kousensai_Order_SYS\app\main.php');

    /**
     * autoLogin
     */
    function autoLogin() {
        session::start();
        // セッション内にIDがある場合はログイン済み
        if (isset($_SESSION['store_id'])) {
            return true;
        }

        // cookie内にトークンがあるかを確認する
        if (isset($_COOKIE['store_login_session'])) {
            // データベースで検索して復元
            $DB = $DB = DB_Connect();
            $sql ="SELECT * FROM `T_STORE_TERMINAL_SESSION` WHERE `login_session_id` = :login_session_id";
            $sql = $DB -> prepare($sql);

            $sql -> bindValue(':login_session_id', $_COOKIE['store_login_session'], PDO::PARAM_STR);
            $sql -> execute();

            $result = $sql -> fetch();

            if ($result == true) {
                $_SESSION['store_id'] = $result['store_id'];
                return true;
            }
        }

        // 復元できない場合は転送
        session::start();
        $URL = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $_SESSION['return_to'] = $URL;
        header("Location: https://kousensai.apori.jp/order/manage/login/?return_to=${URL}");
    }

    /**
     * Login
     * @param string $username
     * @param string $password
     */
    function storeLogin($username, $password) {
        $DB = $DB = DB_Connect();

        $sql = "SELECT * FROM `T_STORE_ACCOUNT` WHERE `account_name` = :account_name;";

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

        unset($DB);

        return Array(
            'store_id' => $result['store_account_id'],
            'account_name' => $result['account_name'],
        );
    }

    //function storeLogout() {}
