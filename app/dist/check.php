<?php


$checker = new ProxyChecker('103.223.15.11', '3139', 'mic', 'michael');
$result = $checker->check();

header('Content-Type: application/json');
echo json_encode($result);


class ProxyChecker
{
    private $url = '/ping.php';
    private $ip;
    private $port;
    private $user;
    private $password;

    public function __construct($ip, $port, $user = null, $password = null)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    public function check()
    {
        $data = $this->request();

        if ($data['http_code'] && $data['http_code'] === 200) {


            echo '<pre>';
            var_dump($data);

            $countryName = $this->getCountryName();
            $result = [
                'status' => 'ok',
                'message' => 'Ok',
                'wait' => '0',
                'data' => [
                    'country' => $countryName ? $countryName : '-',
                    'ip' => $data['outgoing_ip'] ? $data['outgoing_ip'] : '-',
                    'response_time' => $data['total_time'] * 1000
                ],
            ];

        } else {
            $result = [
                'status' => 'error',
                'message' => 'fail',
                'data' => [],
            ];

        }
        return $result;
    }

    private function request()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_PROXYPORT, $this->port);
        curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($ch, CURLOPT_PROXY, $this->ip);
        if ($this->user && $this->password) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, "{$this->user}:{$this->password}");
        }
        $content = curl_exec($ch);
        $data = curl_getinfo($ch);
        curl_close($ch);
        $data['outgoing_ip'] = json_decode($content, true)['ip'] ?? null;
        return $data;
    }

    private function getCountryName()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://www.geoplugin.net/json.gp?ip={$this->ip}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $content = json_decode($content, true);
        return $content['geoplugin_countryName'] ?? null;
    }
}