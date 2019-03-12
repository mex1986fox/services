<?php
namespace App\Services\ApiRequests;

class RequestUpdateTokens
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function go(array $params = [])
    {
        $userID = $params["user_id"];
        $accessToken = $params["access_token"];
        $services = $this->container["services"];
       $serviceAR = $this->container["services"]["asynchreq"];
        $flagErr = false;
        foreach ($services as $key => $servic) {
            if ($key != "asynchreq" && $key != "token") {
               $rparams = 'host=' . $servic["host"] .
                    '&port=' . $servic["port"] .
                    '&sheme=' . $servic["sheme"] .
                    '&url=/api/token/update' .
                    '&data={"user_id":' . $userID . ',"access_token":"'.$accessToken.'"}';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $serviceAR["sheme"]."://".$serviceAR["host"] .":".$serviceAR["port"]. "/api/request/create");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $rparams);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $output = curl_exec($ch);
                curl_close($ch);
                $dj = json_decode($output);
                // echo $output;
                // print_r($dj);
                if (!isset($dj->status) || $dj->status != "ok") {
                    $flagErr = true;
                };
            }
        }
        if ($flagErr == true) {
            return false;
        }
        return true;
    }
}
