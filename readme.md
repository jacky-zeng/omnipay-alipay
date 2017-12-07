# Omnipay: Alipay

[link-donate]

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

    "jacky-zeng/omnipay-alipay": "^1.0",

And run composer to update your dependencies:

    $ composer update

## Basic Usage

The following gateways are provided by this package:

| Gateway       	    		|         Description             |说明                 | Links |
|:---------------	    	|:---------------------------     |:---------         |:----------:|
| Alipay_AopPage 	    		| Alipay Page Gateway             |电脑网站支付 - new    | [Usage][link-wiki-aop-page] [Doc][link-doc-aop-page] |
| Alipay_AopApp 	    		| Alipay APP Gateway              |APP支付 - new    | [Usage][link-wiki-aop-app] [Doc][link-doc-aop-app] |
| Alipay_AopF2F 	    		| Alipay Face To Face Gateway     |当面付 - new         | [Usage][link-wiki-aop-f2f] [Doc][link-doc-aop-f2f] |
| Alipay_AopWap 	    		| Alipay WAP Gateway              |手机网站支付 - new     | [Usage][link-wiki-aop-wap] [Doc][link-doc-aop-wap] |
| Alipay_LegacyApp 	    	| Alipay Legacy APP Gateway       |APP支付      | [Usage][link-wiki-legacy-app] [Doc][link-doc-legacy-app]      |
| Alipay_LegacyExpress 		| Alipay Legacy Express Gateway   |即时到账    | [Usage][link-wiki-legacy-express] [Doc][link-doc-legacy-express]|
| Alipay_LegacyWap      	| Alipay Legacy WAP Gateway   |手机网站支付     | [Usage][link-wiki-legacy-wap] [Doc][link-doc-legacy-wap]       |

## Usage

### Purchase (购买)

```php
    protected function baseOption($gateway)
    {
        $publicKey = config('config.AliPay.alipay_public_key');  //获取支付宝公钥
        $privateKey = config('config.AliPay.merchant_private_key');  //获取支付宝私钥
        //$gateway->setEnvironment('sandbox');
        $gateway->setSignType('RSA2'); // RSA/RSA2/MD5
        $gateway->setAppId(config('config.AliPay.app_id'));
        $gateway->setPrivateKey($privateKey);
        $gateway->setAlipayPublicKey($publicKey);
        return $gateway;
    }

    // alipay - h5/app
    public function aliPay(Request $request)
    {
        $order_number = array_get($request->all(), 'order_number', date('YmdHis') . mt_rand(1000, 9999));
        $total_amount = array_get($request->all(), 'total_amount');
        $gateway = Omnipay::create('Alipay_AopPage');
        $gateway = $this->baseOption($gateway);
        $gateway->setReturnUrl(config('config.AliPay.return_url')); //同步通知地址
        $gateway->setNotifyUrl(config('config.AliPay.notify_url')); //异步通知地址

        $response = $gateway->purchase()->setBizContent([
            'out_trade_no' => $order_number,
            'total_amount' => $total_amount / 100,
            'subject'      => '外卖支付宝支付测试',
            'product_code' => 'FAST_INSTANT_TRADE_PAY',
        ])->send();
        $redirectUrl = $response->getRedirectUrl();
        if ( !$redirectUrl ) return $this->errorResponse('支付宝调用失败', Code::INVALID_REQUEST);
        return $this->successResponse(['pay_url' => $redirectUrl]);
    }
```

For general usage instructions, please see the main [Omnipay](https://github.com/omnipay/omnipay)
repository.

### Refund (退款)
```
    /**
     * 支付宝退款 (有密)
     * @param Request $request
     * @return mixed
     */
    public function aliRefund(Request $request)
    {
        $params = $request->all();
        $order_number = array_get($params, 'order_number');
        $out_trade_no = array_get($params, 'out_trade_no');
        $amount = array_get($params, 'amount');
        $gateway = Omnipay::create('Alipay_LegacyExpress');
        $gateway->setSignType(config('config.AliPay.sign_type'));
        $gateway->setReturnUrl(config('config.AliPay.return_url')); //同步通知地址
        $gateway->setNotifyUrl(config('config.AliPay.notify_url')); //异步通知地址
        $gateway->setSellerEmail(config('config.AliWebPay.seller_email'));
        $gateway->setPartner(config('config.AliWebPay.partner'));
        $gateway->setKey(config('config.AliWebPay.key'));
        $data = [
            'refund_date' => date('Y-m-d H:i:s'),
            "seller_user_id" => trim(config('config.AliWebPay.seller_id')),
            'batch_no' => $order_number,
            'batch_num' => 1,//退款笔数与refund_items数组中保持一致
            '_input_charset' => 'UTF-8',
            'refund_items' => [
                [
                    'out_trade_no' => $out_trade_no,
                    'amount' => $amount / 100.0,
                    'reason' => 'User_refund'
                ]
            ],
        ];
        $request = $gateway->refund($data);
        $response = $request->send();
        $url = $response->getRedirectUrl();
        return $this->successResponse(['url' => $url]); //在浏览器中打开该地址输入密码进行退款
    }

    /**
     * 支付宝退款 （无密）
     * @param Request $request
     * @return mixed
     */
    public function aliRefundNoPwd(Request $request)
    {
        $params = $request->all();
        $order_number = array_get($params, 'order_number');
        $out_trade_no = array_get($params, 'out_trade_no');
        $amount = array_get($params, 'amount');
        $gateway = Omnipay::create('Alipay_LegacyExpress');
        $gateway->setSignType(config('config.AliPay.sign_type'));
        $gateway->setReturnUrl(config('config.AliPay.return_url')); //同步通知地址
        $gateway->setNotifyUrl(config('config.AliPay.notify_url')); //异步通知地址
        $gateway->setSellerEmail(config('config.AliWebPay.seller_email'));
        $gateway->setPartner(config('config.AliWebPay.partner'));
        $gateway->setKey(config('config.AliWebPay.key'));
        $data = [
            'refund_date' => date('Y-m-d H:i:s'),
            "seller_user_id" => trim(config('config.AliWebPay.seller_id')),
            'batch_no' => $order_number,
            'batch_num' => 1,//退款笔数与refund_items数组中保持一致
            '_input_charset' => 'UTF-8',
            'refund_items' => [
                [
                    'out_trade_no' => $out_trade_no,
                    'amount' => $amount / 100.0,
                    'reason' => 'User_refund'
                ]
            ],
        ];
        $request = $gateway->refundNoPwd($data);
        $response = $request->send();
        $url = $response->getRedirectUrl();
        $html = file_get_contents($url);
        $rs = $this->xmlToArray($html);
        if ($rs && array_get($rs, 'is_success') == 'T') {
            return $this->successResponse(['msg' => '申请退款成功']);
        } else {
            if(array_get($rs, 'error') == 'DUPLICATE_BATCH_NO'){
                return $this->errorResponse('请勿重复申请 '. array_get($rs, 'error'), Code::INVALID_REQUEST);
            }else{
                return $this->errorResponse('申请退款失败 '. array_get($rs, 'error'), Code::INVALID_REQUEST);
            }
        }
    }
    
```
## Refund Query （退款查询）
```
   /**
    * 支付宝退款查询
    * @param Request $request
    * @return mixed
    */
   public function aliQueryRefund(Request $request)
   {
       $params = $request->all();
       $out_trade_no = array_get($params, 'out_trade_no');
       $out_request_no = array_get($params, 'out_request_no');
       $gateway = Omnipay::create('Alipay_AopPage');
       $gateway = $this->baseOption($gateway);
       $data = [
           'biz_content' => [
                   'trade_no' => $out_trade_no,
                   'out_request_no' => $out_request_no
               ]
       ];
       $request = $gateway->refundQuery($data);
       $response = $request->send();
       $rs = $response->getData();
       if($rs && is_array($rs)){
           if(array_get($rs, 'alipay_trade_fastpay_refund_query_response.code') == '10000'){
               return $this->successResponse(['msg' => '退款已完成']);
           }else{
               return $this->successResponse([
                   'msg' => array_get($rs, 'alipay_trade_fastpay_refund_query_response.msg')
                       . ' '
                       . array_get($rs, 'alipay_trade_fastpay_refund_query_response.sub_msg')
               ]);
           }
       }else{
           return $this->errorResponse('调用失败 '. array_get($rs, 'error'), Code::INVALID_REQUEST);
       }
   }
```
## Related

- [Laravel-Omnipay](https://github.com/ignited/laravel-omnipay)
- [Omnipay-GlobalAlipay](https://github.com/lokielse/omnipay-global-alipay)
- [Omnipay-WechatPay](https://github.com/lokielse/omnipay-wechatpay)
- [Omnipay-UnionPay](https://github.com/lokielse/omnipay-unionpay)

## Support

If you believe you have found a bug, please email 1017798347@qq.com

[link-donate]: https://cloud.githubusercontent.com/assets/1573211/18808259/a283d596-828f-11e6-8810-4a2e16d5e319.jpg
