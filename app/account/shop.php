<?php
    function getStore($DB) {
        $sql = "SELECT * FROM ORDER_SYS_DB.`T_STORE_INFORMATION` WHERE 1";

        $sql = $DB -> prepare($sql);
        $sql -> execute();
        return $sql -> fetchAll(PDO::FETCH_ASSOC);
    }