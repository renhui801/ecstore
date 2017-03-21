<?php
/**
 * cps_ctl_admin_adlink
 * 推广链接模型层类
 *
 * @uses desktop_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_admin_adlink Jun 20, 2011  4:00:05 PM ever $
 */
class cps_ctl_admin_adlink extends desktop_controller {

    public $workground = 'cps_center';

    /**
     * 初始化构造方法
     * @param object $app
     * @access public
     * @version 1 Jun 23, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 推广链接显示页
     * @access public
     * @version 2 Jul 4, 2011
     */
    public function showAdLink() {
        //获取推广链接
        $arrAds = $this->app->model('adlink')->getAdLinkImageList();
        //设置页面显示数据
        $this->pagedata['data'] = $arrAds;
        //设置json数据
        $this->pagedata['jsdata'] = json_encode($arrAds);
        //输出页面
        $this->page('admin/ad_list.html', $this->app->app_id);
    }

    /**
     * 推广链接编辑页
     * @access public
     * @version 3 Jul 4, 2011
     */
    public function editAdLink() {
        //推广链接模型
        $mdlAdLink = $this->app->model('adlink');
        //推广链接图片模型
        $mdlAdImg = $this->app->model('adlinkpic');

        //获取推广链接id
        $adId = $_GET['linkId'];

        //是否编辑操作
        if ($_POST) {
            //开启事务
            $this->begin();
            //推广链接数据
            $arrAdLink = array(
                'link_id' => $adId,
                'url' => $_POST['url'],
            );

            //推广链接图片数据
            $arrAdImg = array(
                'remote_img_url' => $_POST['remote_img_url'],
                'width' => $_POST['width'][$adId],
                'height' => $_POST['height'][$adId],
            );

            //编辑推广链接
            $rsLink = $mdlAdLink->update($arrAdLink, array('link_id' => $adId));
            //编辑推广链接图片
            $rsImg = $mdlAdImg->update($arrAdImg, array('link_id' => $adId));
            
            //删除原有尺寸
            unset($_POST['width'][$adId]);
            unset($_POST['height'][$adId]);
            //循环新增推广链接图片尺寸,同一个图片的不同尺寸视为不同的广告
            if(!empty($_POST['width'])){
            	foreach($_POST['width'] as $k=>$v){
            		$arrAdLinkNew = array();
            		//推广链接数据
            		$arrAdLinkNew = array(
		                'addtime' => time(), 
		                'url' => $_POST['url'],
		            );
		            $aRsLink = $mdlAdLink->insert($arrAdLinkNew);
		            if(!$aRsLink){
		                $rsLink = false;
		            }
		            
		            $arrAdImg = array();
	            	$arrAdImg['remote_img_url'] = $_POST['remote_img_url'];
	            	$arrAdImg['width'] = $v;
	            	$arrAdImg['height'] = $_POST['height'][$k];
	            	//推广链接与图片关联
	                $arrAdImg['link_id'] = $aRsLink;
	                //编辑推广链接图片
	                $aRsImg = $mdlAdImg->insert($arrAdImg);
	                if(!$aRsImg){
	                	$rsImg = false;
	                }
            	}
            }
            //結果
            $rs = false;
            //提示信息
            $msg = '保存失败';

            //保存成功
            if ($rsLink && $rsImg) {
                $rs = true;
                $msg = '保存成功';
            }

            //结束事务
            $this->end($rs, 'index.php?app=cps&ctl=admin_adlink&act=showAdLink', $msg);
        } else {
            //获取推广链接数据
            $arrAdLink = $mdlAdLink->getAdLinkById($adId);
            //获取推广链接图片数据
            $arrAdImg = $mdlAdImg->getImageById($adId);

            //设置页面显示数据
            $data = array(
                'link_id' => $adId,
                'url' => $arrAdLink['url'],
                'pic_id' => $arrAdImg['pic_id'],
                'remote_img_url' => $arrAdImg['remote_img_url'],
                'width' => $arrAdImg['width'],
                'height' => $arrAdImg['height'],
            );
            $this->pagedata['data'] = $data;

            //显示页面
            $this->page('admin/ad_edit.html', $this->app->app_id);
        }
    }

    /**
     * 推广链接添加页
     * @access public
     * @version 2 Jul 4, 2011
     */
    public function addAdLink() {
        //是否添加操作
        if ($_POST) {
            //推广链接模型
            $mdlAdLink = $this->app->model('adlink');
            //推广链接图片模型
            $mdlAdImg = $this->app->model('adlinkpic');

            //开启事务
            $this->begin();

            //保存图片结果
            $rsImg = true;
            //保存广告结果
            $rsLink = true;
            //循环推广链接图片数据,同一个图片的不同尺寸视为不同的广告
            foreach($_POST['img']['width'] as $k=>$v){
            	$arrAdLink = array();
            	//推广链接数据
            	$arrAdLink = $_POST['link'];
            	//添加时间
            	$arrAdLink['addtime'] = time();
            	//编辑推广链接
            	$aRsLink = $mdlAdLink->insert($arrAdLink);
            	if(!$aRsLink){
                	$rsLink = false;
                }
            	
            	$arrAdImg = array();
            	$arrAdImg['remote_img_url'] = $_POST['img']['remote_img_url'];
            	$arrAdImg['width'] = $v;
            	$arrAdImg['height'] = $_POST['img']['height'][$k];
            	//推广链接与图片关联
                $arrAdImg['link_id'] = $aRsLink;
                //编辑推广链接图片
                $aRsImg = $mdlAdImg->insert($arrAdImg);
                if(!$aRsImg){
                	$rsImg = false;
                }
            }
            //結果
            $rs = false;
            //提示信息
            $msg = '添加失败';

            //保存成功
            if ($rsLink && $rsImg) {
                $rs = true;
                $msg = '添加成功';
            }

            //结束事务
            $this->end($rs, 'index.php?app=cps&ctl=admin_adlink&act=showAdLink', $msg);
        } else {
            //显示页面
            $this->page('admin/ad_add.html', $this->app->app_id);
        }
    }

    /**
     * 推广链接删除
     * @access public
     * @version 2 Jul 4, 2011
     */
    public function delete() {
        //获取GET推广链接id
        $adId = $_GET['linkId'];
        //推广链接删除
        $this->app->model('adlink')->delete(array('link_id' => $adId));
        //推广链接图片删除
        $this->app->model('adlinkpic')->delete(array('link_id' => $adId));
        //页面跳转
        $this->splash('success', 'index.php?app=cps&ctl=admin_adlink&act=showAdLink', '删除成功');
    }
}