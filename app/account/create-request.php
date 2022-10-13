<?php
    require_once(dirname(__DIR__).'\account\create.php');
    require_once(dirname(__DIR__).'\main.php');

    $result = createStoreAccount(
        '会計局',
        'S7id9wLE',
        '会計局'
    );

    echo "result: " . json_encode($result);