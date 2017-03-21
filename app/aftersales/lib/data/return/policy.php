<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 用于获取售后服务的一些数据信息
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package aftersales.lib
 */
class aftersales_data_return_policy extends b2c_api_rpc_request
{
    /**
     * @var model object
     */
    public $rProduct;

    /**
     * @var product list item status
     */
    private $arr_status = array(
        '1' => '申请中',
        '2' => '审核中',
        '3' => '审核通过',
        '4' => '完成',
        '5' => '审核未通过',
    );

    /**
     * 构造方法
     * @param object application
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->rProduct = $this->app->model('return_product');
    }

    /**
     * 得到本类应用的配置参数
     * @param array 参数数组 - 取地址
     * @return boolean true or false
     */
    public function get_conf_data(&$arr_settings)
    {
        $arr_settings['return_product_comment'] = $this->app->getConf('site.return_product_comment');

        return ($arr_settings && is_array($arr_settings)) ? true : false;
    }

    /**
     * 得到满足条件的售后申请列表
     * @param string database table columns
     * @param array conditions
     * @param int page code
     * @return array 结果数组
     */
    public function get_return_product_list($clos='*', $filter = array(), $nPage=1)
    {
        $arr_return_products = array();

        $aData = $this->rProduct->getList($clos,$filter,($nPage-1)*10,10,'add_time DESC');
        $count = $this->rProduct->count($filter);

        return $arr_return_products = array(
            'data' => $aData,
            'total' => $count,
        );
    }

	/**
	 * 改变售后状态
	 * @param mixed sdf 售后信息数组
	 * @return boolean 成功与否
	 */
    public function change_status(&$sdf)
    {
        $is_changed = $this->rProduct->change_status($sdf);

        if ($is_changed)
        {
            $arr_data = $this->rProduct->dump($sdf['return_id']);
            $sdf['return_id'] = $arr_data['return_id'];
            $sdf['order_id'] = $arr_data['order_id'];
            $sdf['status'] = $arr_data['status'];

            return $sdf['status'];
        }
        else
        {
            return false;
        }
    }

    /**
     * 保存售后申请单的信息
     * @param array sdf 标准数组
     * @param string 保存消息
     * @return boolean true or false
     */
    public function save_return_product(&$sdf, &$msg='')
    {
        $sdf['return_id'] = $this->rProduct->gen_id();
        $sdf['return_bn'] = $sdf['return_id'];
        $is_save = $this->rProduct->save($sdf);

        if (!$is_save)
        {
            $msg = '数据保存失败！';

            return false;
        }

        return true;
    }

    /**
     * 得到特定的售后申请单的信息
     * @param string 售后申请单编号
     * @return array 售后的信息数组
     */
    public function get_return_product_by_return_id($return_id=0)
    {
        if (!$return_id)
            return array();

        $arr_data = $this->rProduct->dump($return_id);

        if ($arr_data)
        {
            $arr_data['product_data'] = unserialize($arr_data['product_data']);
            $arr_data['comment'] = unserialize($arr_data['comment']);
            $arr_data['status'] = $this->arr_status[$arr_data['status']];
        }
        return $arr_data;
    }

	/**
	 * 下载指定的售后服务信息的附件
	 * @param string 售后主键ID
	 * @return null
	 */
    public function file_download($return_id=0)
    {
        if ($return_id)
        {
            $rp = $this->app->model('return_product');

			$is_remote = false;
            $info = $rp->dump($return_id);
            $filename = $info['image_file'];
            $obj_images = app::get('image')->model('image');
            $arr_image = $obj_images->dump($filename);
			if (strpos($arr_image['url'],'http://') === false)
				$filename = ROOT_DIR . '/' . $arr_image['url'];
			else
			{
				$is_remote = true;
				$filename = $arr_image['url'];
				$basename = substr($arr_image['url'], strrpos($arr_image['url'],'/')+1);
			}

            if ($filename)
            {
				if (!$is_remote)
				{
					$file = fopen($filename,"r");
					Header("Content-type: image/jpeg");
					Header("Accept-Ranges: bytes");
					Header("Accept-Length: ".filesize($filename));
					Header("Content-Disposition: attachment; filename=".basename($filename));
					echo fread($file,filesize($filename));
					fclose($file);
				}
				else
				{
					Header("Content-type: image/jpeg");
					Header("Accept-Ranges: bytes");
					//Header("Accept-Length: ".filesize($filename));
					Header("Content-Disposition: attachment; filename=".$basename);
					$obj_base_http = kernel::single('base_httpclient');
					echo $obj_base_http->action('GET',$filename);
				}
            }
        }
    }
}
