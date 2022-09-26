<?php
    /**
     * DBへの接続
     */
    function DB_Connect() {
        try {
            $host = DB_CONFIG['DB_HOST'];
            $name = DB_CONFIG['DB_NAME'];

            return new PDO(
                "mysql:host${host};dbname=${name};charset=utf8",
                DB_CONFIG['DB_USER'],
                DB_CONFIG['DB_PASS']
            );
        }catch (PDOExceptison $ex) {
            // DB接続のエラー処理
        }
    }