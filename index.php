<?php
/**
 * @Author: suifengtec
 * @Date:   2017-03-12 16:34:35
 * @Last Modified by:   suifengtec
 * @Last Modified time: 2017-03-14 02:51:18
 */


/*header("Content-type: text/html; charset=utf-8");
*/
header('Content-type: application/json; charset=utf-8');

defined('AOP_SDK_DEV_MODE') || define('AOP_SDK_DEV_MODE', false);

require_once __DIR__. DIRECTORY_SEPARATOR.'lib/SignData.php';
require_once __DIR__. DIRECTORY_SEPARATOR.'lib/EncryptParseItem.php';
require_once __DIR__. DIRECTORY_SEPARATOR.'lib/AopClient.php';
require_once __DIR__. DIRECTORY_SEPARATOR.'lib/ZhimaCreditAntifraudVerifyRequest.php';

/*==========================助手函数==================================*/
function get_zhima_user_true_name_data(){

		/*
		
		V_CN_NA : 查询不到身份证信息;
		V_CN_NM_UM : 姓名与身份证号不匹配;
		V_CN_NM_MA : 姓名与身份证号匹配;
		 */
	$r = array(
/*		'V_CN_NA'=>'查询不到身份证信息',
		'V_CN_NM_UM'=>'姓名与身份证号不匹配',*/
		'V_CN_NM_MA'=>'姓名与身份证号匹配',
		);
	return $r;
}

function get_zhima_user_phone_data(){

	$r = array(

/*		'V_PH_NA'=>'查询不到电话号码信息',
		'V_PH_CN_UM'=>'电话号码与本人不匹配',
		'V_PH_NM_UM'=>'电话号码与姓名不匹配',*/

		'V_PH_CN_MA_UL30D'=>'电话号码与本人匹配，30天内有使用',
		'V_PH_CN_MA_UL90D'=>'电话号码与本人匹配，90天内有使用',
		'V_PH_CN_MA_UL180D'=>'电话号码与本人匹配，180天内有使用',
		'V_PH_CN_MA_UM180D'=>'电话号码与本人匹配，180天内没有使用',
		
		'V_PH_NM_MA_UL30D'=>'电话号码与姓名匹配，30天内有使用',
		'V_PH_NM_MA_UL90D'=>'电话号码与姓名匹配，90天内有使用',
		'V_PH_NM_MA_UL180D'=>'电话号码与姓名匹配，180天内有使用',
		'V_PH_NM_MA_UM180D'=>'电话号码与姓名匹配，180天内没有使用',
		);

	return $r;

}


function get_zhima_user_email_data(){

	$r = array(

/*		'V_EM_PH_UK'=>'EMAIL与手机号码关系未知',
		'V_EM_CN_UK'=>'EMAIL与本人关系未知',
		'V_EM_CN_UM'=>'EMAIL与本人不匹配',
		'V_EM_PH_UM'=>'EMAIL与手机号码不匹配',*/
		'V_EM_CN_MA'=>'EMAIL与本人匹配',
		'V_EM_PH_MA'=>'EMAIL与手机号码匹配',
		'V_EM_PH_MA'=>'EMAIL与手机号码匹配',

		);

	return $r;


}
/*===================================================================*/

/*
!empty($_POST) 
1==1
http://wplms26.com/wp-content/plugins/aboutcg-2017/libss/
 */
if( 1==1  ){



$c = new AopClient;

/*==========================配置参数==================================*/
$c->appId = '20170***';
$c->rsaPrivateKey =  'MIIEoQIBAAKCAQEAw***';
$c->alipayrsaPublicKey =  'MIGfMA0GCSqGSIb3DQEBAQUAA***';
$c->encryptKey =  'JCbiDpmAo***';
$c->alipayPublicKey =  __DIR__. DIRECTORY_SEPARATOR .'alipayPublicKey.pem';


/*============================================================*/
/*==========================业务参数==================================*/
$transaction_id = time().(!empty($_REQUEST['transaction_id'])?$_REQUEST['transaction_id']:'1');
$true_name = !empty($_REQUEST['name'])?$_REQUEST['name']:'李**';
$cert_no = !empty($_REQUEST['id'])?$_REQUEST['id']:'41************';
$mobile = !empty($_REQUEST['phone'])?$_REQUEST['phone']:'';
$email = !empty($_REQUEST['email'])?$_REQUEST['email']:'';
$bank_card = !empty($_REQUEST['bankcard'])?$_REQUEST['bankcard']:'';

/*============================================================*/

$c->format = 'json';
$c->charset = 'UTF-8';
$c->signType = 'RSA';
$c->encryptType = 'AES';





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

$resp = $c->execute( $r );

$r = array('success'=>false);

if(is_object( $resp )&&!empty($resp->zhima_credit_antifraud_verify_response->code)&&!empty($resp->zhima_credit_antifraud_verify_response->msg)){

	$res =  $resp->zhima_credit_antifraud_verify_response;
	if('40004'==$res->code ){
		/*
		身份证号码无效。
		 */
		echo json_encode( $r );
		die();
	}

  	/*
  	返回并通过签名验证
  	 */
	if( $res->code == '10000'&&$res->msg=='Success' ){

		if(in_array('V_CN_NM_UM', $res->verify_code)){
			/*
			姓名与身份证不匹配。
			 */
			echo json_encode( $r );
			die();
		}


		/*==========================按需配置==================================*/
		$data1 = get_zhima_user_true_name_data();
		$data2 = get_zhima_user_phone_data();
		$data3 = get_zhima_user_email_data();
		$tips = array_merge($data1,$data2,$data3);
		/*============================================================*/
		$i = 0;
		foreach( $res->verify_code as $k){

			if(isset( $tips[$k] )){
				$i++;

			}
		}

		if($i){
			$r = array('success'=>true,'msg'=>$res->verify_code,'transaction_id'=>$transaction_id)	;
		}
		echo json_encode( $r );
		die();	

	}else{
		/*
		稍后重试！因为有时候会返回系统繁忙。。。
		 */
		echo json_encode( $r );
		die();	
	}


}


}/*//POST*/

?>