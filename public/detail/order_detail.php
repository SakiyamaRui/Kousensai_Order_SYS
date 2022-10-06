<?php
    require_once(dirname(__DIR__).'/path.php');

    preg_match('/^\/detail\/([\w\/]*)\/?/', $_SERVER['REQUEST_URI'], $matches);
    $request = explode('/', $matches[1]);

    $order_id = $request[0];


    require_once(ROOT_PATH.'\template\costomer\order-detail.html');