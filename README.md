# ZhimaCreditAntifraud-SDK

芝麻信用反欺诈 PHP 极速版SDK 。芝麻信用是蚂蚁金服推出的一个基于阿里系大数据的身份真实性检测API,这是一个与该接口相关的快速SDK。

## 使用

```

defined('AOP_SDK_DEV_MODE') || define('AOP_SDK_DEV_MODE', false);
defined('ZHIMA_AF_SDK_BASE_DIR') || define('ZHIMA_AF_SDK_BASE_DIR', __DIR__. DIRECTORY_SEPARATOR);

require_once ZHIMA_AF_SDK_BASE_DIR.'SignData.php';
require_once ZHIMA_AF_SDK_BASE_DIR'EncryptParseItem.php';
require_once ZHIMA_AF_SDK_BASE_DIR'AopClient.php';
require_once ZHIMA_AF_SDK_BASE_DIR'ZhimaCreditAntifraudVerifyRequest.php';


```

先载入各个类文件(除修改了一处`AopClient.php `中的判断不妥之外,其它均忠实与蚂蚁金服的官方SDK)；

然后,实例化客户端类,并设置必需的配置参数:
```

$c = new AopClient;

/*==========================配置参数==================================*/
$c->appId = '20170***';
$c->rsaPrivateKey =  'MIIEoQIBAAKCAQEAw***';
$c->alipayrsaPublicKey =  'MIGfMA0GCSqGSIb3DQEBAQUAA***';
$c->encryptKey =  'JCbiDpmAo***';
/*就放在同一个目录下*/
$c->alipayPublicKey =  ZHIMA_AF_SDK_BASE_DIR .'alipayPublicKey.pem';
/*============================================================*/


```

然后,设置业务参数:
```
$args = array(
    /*芝麻反欺诈,固定值*/
    'product_code'=> 'w1010100000000002859',
    'transaction_id'=> $transaction_id ,
    'cert_no'=> $cert_no,
    'cert_type'=> 'IDENTITY_CARD',
    'name'=> $true_name ,
    'mobile'=> $mobile ,
    'email'=> $email ,
    'bank_card' =>$bank_card,
);
$bizContent =  json_encode($args);

$r = new ZhimaCreditAntifraudVerifyRequest();
$r->setBizContent($bizContent);


```

然后, 发起请求,并获取响应:
```

$resp = $c->execute( $r );

```

然后,解析响应,实际例子见用例。

## 用例

见 index.php 中的用例, 用例如果仅输入姓名和身份证号码,并且姓名和身份证对应,并且存在于支付宝时,将会返回如下数据:
```

{"success":true,"msg":["V_CN_NM_MA"],"transaction_id":"14894307711"}

```

用例中仅对返回做了简单的解析,如需更多详细的解析,请自行完成。
