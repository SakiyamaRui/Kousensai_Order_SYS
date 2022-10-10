<?php
    function userOderList($token, $DB = null) {
        $DB_flag = false;

        if ($DB == null) {
            $DB = DB_Connect();
            $DB_flag = true;
        }

        // DBで検索
        $sql = "SELECT 
                    `token`,
                    `order_id`,
                    `confirmed_order_flag`,
                    `order_time`
                FROM
                    ORDER_SYS_DB.`T_ORDER_INFORMATION_MAIN`
                WHERE
                    `session_token` = :session_token
                ORDER BY
                    `order_time` ASC;";
        $sql = $DB -> prepare($sql);

        $sql -> bindValue(':session_token', $token, PDO::PARAM_STR);
        $sql -> execute();

        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

        if (count($record) == 0 || $record == false) {
            if ($DB_flag) {
                unset($DB);
            }

            return Array();
        }

        // フォーマットを変更する
        $order_list = Array();
        foreach ($record as $val) {
            $status = '支払い待ち';

            if ($val['confirmed_order_flag'] == 1) {
                $status = '注文確定済み';
            }

            array_push($order_list, Array(
                'token' => $val['token'],
                'order_id' => $val['order_id'],
                'order_time' => $val['order_time'],
                'status' => $status
            ));
        }

        return $order_list;

    }