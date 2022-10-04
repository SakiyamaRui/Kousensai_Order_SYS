<?php
    require_once('C:\Users\ic211216\Desktop\創造研究\Kousensai_Order_SYS\app\main.php');

    preg_match('/^\/manage\/([\w\/]*)\/?/', $_SERVER['REQUEST_URI'], $matches);
    $request = explode('/', $matches[1]);

    switch ($request[0]) {
        case 'login':
            require_once(ROOT_PATH .'\template\store\login.html');
            break;

        case 'item':
            $type = '登録';
            require_once(ROOT_PATH.'\template\item\item-edit.html');
            break;

        case '':
            break;
    }