### 注意
HTTP 方式接入不支持非托管链账户，请使用官方 Java SDK 或 Go SDK

### install
```
composer require "vipszx/antBlockchain"
```

### usage

```
use vipszx\antBlockchain\client;

$client = new client([
            'accessId' => 'accessId',
            'privateKey' => './access.key',
            'bizId' => 'a00e36c5',
            'tenantId' => 'tenantId',
            'gas' => 1000000,
        ]);
       
 //存证
 $clinet->deposit($orderId, $content, $account, $mykmsKeyId, $gas = null);
 
 //异步调用 Solidity 合约
 $client->callSolidityContract($orderId, $account, $mykmsKeyId, $contractName, $methodSignature, $inputParamListStr, $outTypes, $gas = null);
        
//查询交易
$this->queryTransaction($hash);

//查询交易回执
$this->queryReceipt($hash);

 // 查询账户
 $client->queryAccount($account);
```
