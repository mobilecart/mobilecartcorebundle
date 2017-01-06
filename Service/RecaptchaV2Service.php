<?php

namespace MobileCart\CoreBundle\Service;

class RecaptchaV2Service
{
    protected $privateKey = '';

    protected $publicKey = '';

    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
        return $this;
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function isValid($response, $ip='')
    {
        $params = [
            'secret' => $this->getPrivateKey(),
            'response' => $response,
        ];

        if ($ip) {
            $params['remoteip'] = $ip;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';

        //url-ify the data for the POST
        $postStr = '';
        foreach($params as $key=>$value) { $postStr .= $key . '=' . $value . '&'; }
        rtrim($postStr, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);// count($params));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postStr);
        $json = curl_exec($ch);
        curl_close($ch);

        $obj = @json_decode($json);

        return is_object($obj) && isset($obj->success)
            ? $obj->success
            : false;
    }

}
