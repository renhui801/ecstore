<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


/**
 * 外部支付接口统一调用的api类
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment
 */
class ectools_payment_api
{
	/**
	 * @var object 应用对象的实例。
	 */
	private $app;

	/**
	 * 构造方法
	 * @param object 当前应用的app
	 * @return null
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}

	/**
	 * 支付返回后的同意支付处理
	 * @params array - 页面参数
	 * @return null
	 */
	public function parse($params='')
	{
		// 取到内部系统参数
		$arr_pathInfo = explode('?', $_SERVER['REQUEST_URI']);
		$pathInfo = substr($arr_pathInfo[0], strpos($arr_pathInfo[0], "parse/") + 6);
		$objShopApp = $this->getAppName($pathInfo);
		$innerArgs = explode('/', $pathInfo);
		$class_name = array_shift($innerArgs);
		$class_name = array_shift($innerArgs);
		$method = array_shift($innerArgs);

		$arrStr = array();
		$arrSplits = array();
		$arrQueryStrs = array();
		// QUERY_STRING
		if (isset($arr_pathInfo[1]) && $arr_pathInfo[1]){
			$querystring = $arr_pathInfo[1];
		}
		if ($querystring)
		{
			$arrStr = explode("&", $querystring);

			foreach ($arrStr as $str)
			{
				$arrSplits = explode("=", $str);
				$arrQueryStrs[urldecode($arrSplits[0])] = urldecode($arrSplits[1]);
			}
		}
		else
		{
			if ($_POST)
			{
				$arrQueryStrs = $_POST;
			}
		}

        logger::info("支付返回信息记录：".var_export($arrQueryStrs,1));
		$payments_bill = new $class_name($objShopApp);
		$ret = $payments_bill->$method($arrQueryStrs);
        logger::info("支付返回信息转换之后记录：".var_export($ret,1));
		// 支付结束，回调服务.
		if (!isset($ret['status']) || $ret['status'] == '') $ret['status'] = 'failed';

		$obj_payments = app::get('ectools')->model('payments');
		$sdf = $obj_payments->dump($ret['payment_id'], '*', '*');
		if ($sdf){
			$sdf['account'] = $ret['account'];
			$sdf['bank'] = $ret['bank'];
			$sdf['pay_account'] = $ret['pay_account'];
			$sdf['currency'] = $ret['currency'];
			$sdf['trade_no'] = $ret['trade_no'];
			$sdf['t_payed'] = $ret['t_payed'];
			$sdf['pay_app_id'] = $ret['pay_app_id'];
			$sdf['pay_type'] = $ret['pay_type'];
			$sdf['memo'] = $ret['memo'];
			$sdf['money'] = $ret['money'];
			$sdf['cur_money'] = $ret['cur_money'];
		}

		switch ($ret['status']){
			case 'succ':
			case 'progress':
				if ($sdf && $sdf['status'] != 'succ')
				{
					$is_updated = false;
					$obj_payment_update = kernel::single('ectools_payment_update');
					$is_updated = $obj_payment_update->generate($ret, $msg);

					$obj_pay_lists = kernel::servicelist("order.pay_finish");
					foreach ($obj_pay_lists as $order_pay_service_object)
					{
						// 防止重复充值
						if ($is_updated)
						{
							$db = kernel::database();
							$transaction_status = $db->beginTransaction();
							$is_updated = $order_pay_service_object->order_pay_finish($sdf, $ret['status'], 'font',$msg);
							if (!$is_updated)
							{
								logger::error(app::get('ectools')->_('支付失败') . " " . $msg ."\n");
								$db->rollback();
							}
							else
							{
								$db->commit($transaction_status);
								// 支付扩展事宜 - 如果上面与中心没有发生交互，那么此处会发出和中心交互事宜.
								if (method_exists($order_pay_service_object, 'order_pay_finish_extends')){
                                    $sdf['status'] = $ret['status'];
									$order_pay_service_object->order_pay_finish_extends($sdf);
                                }
							}
						}
					}
					//支付成功给支付网关显示支付信息
					if(method_exists($payments_bill, 'ret_result')){
						$payments_bill->ret_result($ret['payment_id']);
					}
				}
				break;
			case 'REFUND_SUCCESS':
				// 退款成功操作
				if ($sdf){
					unset($sdf['payment_id']);
					$obj_refund = app::get('ectools')->model('refund');
					$sdf['refund_id'] = $obj_refund->gen_id();
					$ret['status'] = 'succ';
					if ($obj_refund->insert($sdf)){
						//处理单据的支付状态
						$obj_refund_finish = kernel::service("order.refund_finish");
						$obj_refund_finish->order_refund_finish($sdf, $ret['status'], 'font',$msg);
					}
				}
				break;
			case 'PAY_PDT_SUCC':
				$ret['status'] = 'succ';
				// 无需更新状态.
				break;
			case 'failed':
			case 'error':
			case 'cancel':
			case 'invalid':
			case 'timeout':
				$is_updated = false;
				$obj_payment_update = kernel::single('ectools_payment_update');
				$is_updated = $obj_payment_update->generate($ret, $msg);
				break;
		}

		// Redirect page.
		if ($sdf['return_url']){
			header('Location: '.strtolower(kernel::request()->get_schema().'://'.kernel::request()->get_host()).$sdf['return_url']);
		}
	}

	/**
	 * 得到实例应用名
	 * @params string - 请求的url
	 * @return object - 应用实例
	 */
	private function getAppName($strUrl='')
	{
		//todo.
		if (strpos($strUrl, '/') !== false)
		{
			$arrUrl = explode('/', $strUrl);
		}
		return app::get($arrUrl[0]);
	}
}
