<?php
    require_once(dirname(__DIR__).'/path.php');

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