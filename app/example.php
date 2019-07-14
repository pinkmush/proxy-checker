<?php

namespace ProxyChecker;

include __DIR__ . '/ProxyChecker.php';

$pingUrl = 'http://39eeb39c.ngrok.io/ping.php';
$proxyChecker = new ProxyChecker($pingUrl);

$proxies = array(
//    '183.95.132.76:80',
//    '195.5.18.41:8118',
    '103.223.15.11:3138,miki:michael',
);

$results = $proxyChecker->checkProxies($proxies);

echo '<pre>';
var_export($results);
echo '</pre';
