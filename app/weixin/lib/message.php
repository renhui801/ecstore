<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
//消息
class weixin_message{

    /**
    * @var array 节点数组
    * @access private
    */
    private $_message_objects = array();
    /**
    * @var array 节点数组
    * @access private
    */
    private $_message_maps = array();

    private $_all_message = null;

    /**
    * 构造方法,实例化MODEL
    */
    function __construct() 
    {
        $this->messageModel = app::get('weixin')->model('message');
        $this->msgTextModel = app::get('weixin')->model('message_text');
        $this->msgImageModel = app::get('weixin')->model('message_image');
    }//End Function


    /**
     * 根据message_id,和 message_type 获取到对于的消息
     *
     * @params int    $msg_id     消息ID
     * @params string $msg_type   消息类型 图文|文字
     * @params string $urlParams  传入验证微信用户信息
     */
    public function get_message($msg_id, $msg_type='text', $urlParams){
        if( empty($msg_id) ) return '';
        //文字消息
        if( $msg_type == 'text' ){
            $data = $this->get_msg_text_row($msg_id, $urlParams);
        }else{//图文消息
            $data = $this->get_msg_image_row($msg_id, $urlParams);
        }
        return $data;
    }

    /**
     * 获取一条图文消息
     * @params $msg_id 图文消息ID
     */
    public function get_msg_image_row($msg_id, $urlParams){
        $return = array();
        $topImageData = $this->msgImageModel->getList('title,description,picurl,url,is_check_bind',array('id'=>$msg_id));
        $childrensData = $this->get_message_images($msg_id);
        $data = array_merge($topImageData,$childrensData);
        if(!empty($data)){
            $return['MsgType'] = 'news';
            $return['ArticleCount'] = count($data);
            foreach($data as $key=>$row){
                if( $row['url'] && stristr($row['url'], '?' ) ){
                    $url = $row['url'].'&'.$urlParams;
                }else{
                    $url = $row['url'] ? $row['url'].'?'.$urlParams : '';
                }
                $return['Articles'][$key]['Title']        = $row['title'];
                $return['Articles'][$key]['Description']  = $row['description'] ? $row['description'] : '';
                $return['Articles'][$key]['PicUrl']       = base_storager::image_path($row['picurl']);
                $return['Articles'][$key]['Url']          = $urlParams ? $url : $row['url'];
            }
        }
        return $return;
    }

    //获取一条文字消息
    public function get_msg_text_row($msg_id, $urlParams){
        $data = array();
        $messageTextData = $this->msgTextModel->getList('*',array('id'=>$msg_id) );
        $content = trim($messageTextData[0]['content']);
        $arrUrl = preg_match_all("/href[\s]*?=[\s]*?[\'|\"](.+?)[\'|\"]/",$content,$match);
        foreach((array)$match[1] as $url){
            if( stristr($url, '?' ) ){
                $tmp_url = $url.'&'.$urlParams;
            }else{
                $tmp_url = $url.'?'.$urlParams;
            }
            $content = str_replace( $url, $tmp_url, $content);
        }
        if( $messageTextData ){
            $data['Content'] = $content;
            $data['MsgType'] = 'text';
        }
        return $data;
    }

    public function get_listmaps($id=0, $step=null) 
    {
        $rows = $this->get_maps($id,$step);
        return $this->parse_listmaps($rows);
    }//End Function

    /**
    * 节点的map
    * @param int $id 节点id
    * @param int $setp 路径
    * @return array 节点路由
    */
    public function get_maps($id=0, $step=null) 
    {
        $step_key = (is_null($step)) ? 'all' : $step;
        if(!isset($this->_message_maps[$id][$step_key])){
            $rows = $this->get_message_images($id);
            $step = ($step==null) ? $step : $step-1;
            foreach($rows AS $k=>$v){
                if($v['has_children']=='true' && ($step==null || $step>=0)){
                    $rows[$k]['childrens'] = $this->get_maps($v['id'], $step);
                }
            }
            $this->_message_maps[$id][$step_key] = $rows;
        }
        return $this->_message_maps[$id][$step_key];
    }//End Function

    /**
    * 父节点下的子节点数据
    * @param int $parent_id 父节点id
    * @return 节点数组值
    */
    public function get_message_images($parent_id=0) 
    {
        $parent_id = intval($parent_id);
        if(is_null($this->_all_message)){
            $this->_all_message = array();
            $messageImageData = $this->msgImageModel->select()->order('ordernum ASC')->instance()->fetch_all();
            foreach($messageImageData AS $row){
                $this->_all_message[$row['parent_id']][] = $row;
            }
        }
        return $this->_all_message[$parent_id]; 
    }//End Function

    /**
    * 格式化节点的map
    * @param array $rows 节点MAP
    * @param array
    */
    private function parse_listmaps($rows)
    {
        $data = array();
        foreach((array)$rows AS $k=>$v){
            $children = $v['childrens'];
            if(isset($v['childrens']))  unset($v['childrens']);
            $data[] = $v;
            if($children){
                $data = array_merge($data, $this->parse_listmaps($children));
            }
        }
        return $data;
    }//End Function


}//End Class
