<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_goods_share extends dbeav_model{

    var $api = array(
        'sina' => 'http://service.weibo.com/share/share.php',
        'tencent' => 'http://share.v.t.qq.com/index.php',
        'qzone' => 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey',
        'renren' => 'http://widget.renren.com/dialog/share',
        'kaixin001' => 'http://www.kaixin001.com/rest/records.php',
        'douban' => 'http://shuo.douban.com/!service/share',
    );


    function __construct(&$app){
        $this->app = $app;
        $name_type = array(
            'sina' => app::get('b2c')->_('新浪微博'),
            'tencent' => app::get('b2c')->_('腾讯微博'),
            'qzone' => app::get('b2c')->_('QQ空间'),
            'renren' => app::get('b2c')->_('人人网'),
            'kaixin001' => app::get('b2c')->_('开心网'),
            'douban' => app::get('b2c')->_('豆瓣网'),
        );
        $this->columns = array(
            'name'=>array('label'=>app::get('b2c')->_('分享平台'),'width'=>200,'type'=>$name_type),
            'status'=>array('label'=>app::get('b2c')->_('状态'),'width'=>100,'type'=>array('1'=>app::get('b2c')->_('开启'),'0'=>app::get('b2c')->_('关闭'))),
            'order_by'=>array('label'=>app::get('b2c')->_('排序'),'width'=>200),
        );

        $this->schema = array(
            'default_in_list'=>array_keys($this->columns),
            'in_list'=>array_keys($this->columns),
            'idColumn'=>'gnotify_id',
            'columns'=>$this->columns
        );
    }

    /**
     * suffix of model
     * @params null
     * @return string table name
     */
    public function table_name(){
        return 'goods_share';
    }

    function get_schema(){
        return $this->schema;
    }

    //返回接口的数量
    function count($filter=''){
        return count($this->api);
    }


     /**
     * 取到服务列表 - 1条或者多条
     * @params string - 特殊的列名
     * @params array - 限制条件
     * @params 偏移量起始值
     * @params 偏移位移值
     * @params 排序条件
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null){
        $share = app::get('b2c')->getConf('share_api');
        if(!$share){
            $share = $this->api;
        }else{
            $share += $this->api;
        }

        $data = array();
        foreach($share as $key=>$value){
            if(isset($filter['name']) && $filter['name'] != $key) continue;
            if(isset($filter['status']) && $filter['status'] != $value['status']) continue;
            $row['name'] = $key;
            if(is_array($value)){
                $row['order_by'] = !empty($value['order_by']) ? $value['order_by'] : 0;
                $row['status'] = $value['status'] ? true : false;
            }else{
                $row['order_by'] = 0;
                $row['status'] = false;
            }
            if(isset($value['appkey'])){
                $row['appkey'] = $value['appkey'];
            }
            $row['api'] = $this->api[$key];
            $data_key = $row['order_by'].'_'.$key;
            $data[$data_key] = $row;
        }
        if($orderby == 'order_by' && $data){
            ksort($data);
        }
        return $data;
    }

    function save($data,$mustUpdate = null,$mustInsert = false){
        $share = app::get('b2c')->getConf('share_api');
        if(!$share){
            app::get('b2c')->setConf('share_api',$data);
        }else{
            $share_name = key($data);
            $share[$share_name] = $data[$share_name];
            app::get('b2c')->setConf('share_api',$share);
        }
        return true;
    }

}


