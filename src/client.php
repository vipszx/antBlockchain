<?php

namespace vipszx\antBlockchain;

use GuzzleHttp\Client as httpClient;

class client
{
    const ROOT_URL = 'https://rest.baas.alipay.com';

    const SHAKEHAND_PATH = '/api/contract/shakeHand';
    const TRANSACTION_PATH = '/api/contract/chainCallForBiz';
    const QUERY_PATH = '/api/contract/chainCall';

    private $accessId;
    private $privateKey;
    private $bizId;
    private $tenantId;
    private $token = null;
    private $gas = null;

    public function __construct($setting)
    {
        $this->accessId = $setting['accessId'];
        $this->privateKey = $setting['privateKey'];
        $this->bizId = $setting['bizId'];
        $this->tenantId = $setting['tenantId'];
        $this->token = $setting['token'] ?? null;
        $this->gas = $setting['gas'] ?? 1000000;
    }

    public function shakeHand()
    {
        $url = self::ROOT_URL . self::SHAKEHAND_PATH;
        $microtime = self::microtime();

        $request = [
            'accessId' => $this->accessId,
            'time' => $microtime,
            'secret' => $this->sign($microtime),
        ];

        $httpClient = new httpClient();
        $response = $httpClient->post($url, ['json' => $request]);
        $result = json_decode($response->getBody(), true);

        if (!is_array($result) || $result['success'] === false) {
            throw new \Exception('shake hand fail ' . $response->getBody());
        }

        return $result;
    }

    public function deposit($orderId, $content, $gas = null, $account = null, $mykmsKeyId = null)
    {
        $url = self::ROOT_URL . self::TRANSACTION_PATH;
        $request = [
            'orderId' => $orderId,
            'bizid' => $this->bizId,
            'account' => $account,
            'content' => $content,
            'mykmsKeyId' => $mykmsKeyId,
            'method' => 'DEPOSIT',
            'accessId' => $this->accessId,
            'token' => $this->getToken(),
            'gas' => $gas ?: $this->gas,
            'tenantid' => $this->tenantId,
        ];

        $httpClient = new httpClient();
        $response = $httpClient->post($url, ['json' => $request]);
        $result = json_decode($response->getBody(), true);

        if (!is_array($result) || $result['success'] === false) {
            throw new \Exception($result['data']);
        }

        return $result;
    }

    public function queryTransaction($hash)
    {
        $url = self::ROOT_URL . self::QUERY_PATH;
        $request = [
            'bizid' => $this->bizId,
            'method' => 'QUERYTRANSACTION',
            'hash' => $hash,
            'accessId' => $this->accessId,
            'token' => $this->getToken(),
        ];

        $httpClient = new httpClient();
        $response = $httpClient->post($url, ['json' => $request]);
        $result = json_decode($response->getBody(), true);

        if (!is_array($result) || $result['success'] === false) {
            throw new \Exception('query transaction fail ' . $response->getBody());
        }

        return $result;
    }

    public function queryReceipt($hash)
    {
        $url = self::ROOT_URL . self::QUERY_PATH;
        $request = [
            'bizid' => $this->bizId,
            'method' => 'QUERYRECEIPT',
            'hash' => $hash,
            'accessId' => $this->accessId,
            'token' => $this->getToken(),
        ];

        $httpClient = new httpClient();
        $response = $httpClient->post($url, ['json' => $request]);
        $result = json_decode($response->getBody(), true);

        if (!is_array($result) || $result['success'] === false) {
            throw new \Exception('query receipt fail ' . $response->getBody());
        }

        return $result;
    }

    public function queryAccount($account)
    {
        $url = self::ROOT_URL . self::QUERY_PATH;
        $request = [
            'bizid' => $this->bizId,
            'method' => 'QUERYACCOUNT',
            'requestStr' => json_encode(['queryAccount' => $account]),
            'accessId' => $this->accessId,
            'token' => $this->getToken(),
        ];

        $httpClient = new httpClient();
        $response = $httpClient->post($url, ['json' => $request]);
        $result = json_decode($response->getBody(), true);

        if (!is_array($result) || $result['success'] === false) {
            throw new \Exception('query account fail ' . $response->getBody());
        }

        return $result;
    }

    public function callWasmContract($orderId, $account, $mykmsKeyId, $contractName, $methodSignature, $inputParamListStr, $outTypes, $gas)
    {
        $url = self::ROOT_URL . self::TRANSACTION_PATH;
        $request = [
            'orderId' => $orderId,
            'bizid' => $this->bizId,
            'account' => $account,
            'contractName' => $contractName,
            'methodSignature' => $methodSignature,
            'mykmsKeyId' => $mykmsKeyId,
            'method' => 'CALLWASMCONTRACT',
            'inputParamListStr' => $inputParamListStr,
            'outTypes' => $outTypes,
            'accessId' => $this->accessId,
            'token' => $this->getToken(),
            'gas' => $gas ?: $this->gas,
            'tenantid' => $this->tenantId,
        ];

        $httpClient = new httpClient();
        $response = $httpClient->post($url, ['json' => $request]);
        $result = json_decode($response->getBody(), true);

        if (!is_array($result) || $result['success'] === false) {
            throw new \Exception('call wasm contract fail ' . $response->getBody());
        }

        return $result;
    }

    public function callSolidityContract($orderId, $account, $mykmsKeyId, $contractName, $methodSignature, $inputParamListStr, $outTypes, $gas = null)
    {
        $url = self::ROOT_URL . self::TRANSACTION_PATH;
        $request = [
            'orderId' => $orderId,
            'bizid' => $this->bizId,
            'account' => $account,
            'contractName' => $contractName,
            'methodSignature' => $methodSignature,
            'mykmsKeyId' => $mykmsKeyId,
            'method' => 'CALLCONTRACTBIZASYNC',
            'inputParamListStr' => $inputParamListStr,
            'outTypes' => $outTypes,
            'accessId' => $this->accessId,
            'token' => $this->getToken(),
            'gas' => $gas ?: $this->gas,
            'tenantid' => $this->tenantId,
        ];

        $httpClient = new httpClient();
        $response = $httpClient->post($url, ['json' => $request]);
        $result = json_decode($response->getBody(), true);

        if (!is_array($result) || $result['success'] === false) {
            throw new \Exception('call solidity contract fail ' . $response->getBody());
        }

        return $result;
    }

    public function getToken()
    {
        if ($this->token) {
            return $this->token;
        }

        $result = $this->shakeHand();
        return $result['data'];
    }

    public function sign($microtime)
    {
        $message = $this->accessId . $microtime;

        $privateKey = file_get_contents($this->privateKey);
        $key = openssl_pkey_get_private($privateKey);

        openssl_sign($message, $signature, $key, 'SHA256');
        openssl_free_key($key);

        return bin2hex($signature);
    }

    public static function microtime()
    {
        return ceil(microtime(true) * 1000);
    }
}