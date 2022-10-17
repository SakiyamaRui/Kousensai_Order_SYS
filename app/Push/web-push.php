<?php
    require_once(dirname(__DIR__).'/main.php');
    require_once(ROOT_PATH . '/vendor/autoload.php');

    define('pushConfig', parse_ini_file(ROOT_PATH.'/config/push.ini', false));

    use Minishlink\WebPush\WebPush;
    use Minishlink\WebPush\Subscription;
    $auth = Array(
        'VAPID' => Array(
            'subject' => pushConfig['url'],
            'publicKey' => pushConfig['publicKey'],
            'privateKey' => pushConfig['privateKey']
        )
    );

    function pushNotice($options) {
        global $auth;
        
        $webPush = new WebPush($auth);

        $subscription = Subscription::create(Array(
            'endpoint' => $options['terminal']['endPoint'],
            'publicKey' => $options['terminal']['userPublicKey'],
            'authToken' => $options['terminal']['userAuthToken']
        ));

        $report = $webPush -> sendOneNotification(
            $subscription,
            $options['body']
        );

        $endPoint = $report -> getRequest() -> getUri() -> __toString();

        if ($report -> isSuccess()) {
            return true;
        }else{
            return false;
        }
    }
