<?php
namespace App\Services\ApiRequests;

class RequestToToken
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function go(string $url, array $params = [])
    {

        $sToken = $this->container["services"]["token"];
        $jsonDataEncoded = json_encode($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sToken["sheme"]."://".$sToken["host"] .":".$sToken["port"] . $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $rCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        $dj = json_decode($output);

        if (isset($dj->status) && $dj->status == "ok") {
            return true;
        };
        return false;

    }
}
