<?php 
    function pushAllCorrect($order_id_list, $DB) {
        $inClause = substr(str_repeat(',?', count($order_id_list)), 1);
        $sql = "SELECT 
                    `end_point`,
                    `public_key`,
                    `auth_token`,
                    `T_ORDER_INFORMATION_MAIN`.`token`,
                    `T_ORDER_INFORMATION_MAIN`.`order_id`
                FROM
                    `T_NOTICE_DATA`
                INNER JOIN
                    `T_ORDER_INFORMATION_MAIN` ON `T_NOTICE_DATA`.`session_token` = `T_ORDER_INFORMATION_MAIN`.`session_token`
                WHERE
                    `T_ORDER_INFORMATION_MAIN`.`order_id` IN(%s) AND
                    `T_NOTICE_DATA`.`end_point` IS NOT NULL;";
        $sql = $DB -> prepare(sprintf($sql, $inClause));
        $sql -> execute($order_id_list);
        $record = $sql -> fetchAll();

        $result = Array();
        foreach ($record as $val) {
            $result[$val['order_id']] = pushNotice(Array(
                'terminal' => Array(
                    'endPoint' => $val['end_point'],
                    'userPublicKey' => $val['public_key'],
                    'userAuthToken' => $val['auth_token']
                ),
                'body' => base64_encode(urlencode(json_encode(Array(
                    'title' => '商品が完成しました',
                    'body' => 'すべての商品が完成しました',
                    'order_id' => $val['token'],
                    'type' => 'costomer'
                ))))
            ));
        }

        return $result;
    }

    function pushOrder($order_id, $DB) {
        // 店舗一覧を取得
        $sql = "SELECT
                    `store_id`
                FROM
                    `T_PRODUCT_INFORMATION`
                INNER JOIN
                    `T_ORDER_INFORMATION_DETAIL` ON `T_PRODUCT_INFORMATION`.`product_id` = `T_ORDER_INFORMATION_DETAIL`.`product_id`
                WHERE
                    `T_ORDER_INFORMATION_DETAIL`.`order_id` = :order_id";
        $sql = $DB -> prepare($sql);
        $sql -> bindValue(':order_id', $order_id, PDO::PARAM_STR);
        $sql -> execute();
        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);
        
        if ($record == false) {
            return false;
        }

        $store_id = array_column($record, 'store_id');

        // ログインしているアカウントを全件取得して送信
        $inClause = substr(str_repeat(',?', count($store_id)), 1);
        $sql = "SELECT
                    `end_point`,
                    `public_key`,
                    `auth_token`
                FROM
                    `T_NOTICE_DATA`
                INNER JOIN
                    `T_STORE_TERMINAL_SESSION` ON `T_NOTICE_DATA`.`session_token` = `T_STORE_TERMINAL_SESSION`.`session_token`
                WHERE
                    `store_id` IN(%s) AND
                    `deleted` = 0";
        $sql = $DB -> prepare(sprintf($sql, $inClause));
        $sql -> execute($store_id);
        $record = $sql -> fetchAll(PDO::FETCH_ASSOC);

        foreach ($record as $val) {
            if ($val['end_point'] == null || $val['public_key'] == null || $val['auth_token'] == null) {
                continue;
            }

            pushNotice(Array(
                'terminal' => Array(
                    'endPoint' => $val['end_point'],
                    'userPublicKey' => $val['public_key'],
                    'userAuthToken' => $val['auth_token']
                ),
                'body' => base64_encode(urlencode(json_encode(Array(
                    'order_id' => $order_id,
                    'type' => 'shop'
                ))))
            ));
        }

    }