<?php

class weixin_object {

    /**
     * 绑定公众账号生成微信访问的URL地址
     *
     * @return url
     */
    public function get_weixin_url($eid){
        if( !$eid ){
            $eid = time();
        }
        $base_url = kernel::base_url(1).'/index.php/openapi/weixin/api?eid='.$eid;
        return  $base_url;
    }

    public function get_eid(){
        do{
            $eid = $this->randomkeys(6);
            $row = app::get('weixin')->model('bind')->count(array('eid'=>$eid));
        }while($row);

        return $eid;
    }

    /**
     * 绑定公众账号生成和微信通信的token
     *
     * @return toekn
     */
    public function set_token(){
        return md5('weixin'.$this->randomkeys(12));
    }

    /**
     * 获取到微信和wep通信的token
     *
     * @params int $eid 公众账号的eid
     * @return string $token
     */
    public function get_token($eid){
        $url = $this->get_weixin_url($eid);
        $token =  app::get('weixin')->model('bind')->getList('token',array('url'=>$url));
        return $token[0]['token'];
    }

    /**
     * 设置对访问的微信用户进行签名保存，将返回值带入到url中
     * 用来验证访问的微信用户
     *
     * @params string $post 微信post发送的xml转换后的数组
     */
    public function set_wechat_sign($postData){
        $shopex_token = base_certificate::get('token');
        $tmpArr = array($shopex_token, $postData['FromUserName']);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $signature = sha1( $tmpStr );
        $wechat_sign = 'signature='.$signature.'&openid='.$postData['FromUserName'].'&u_eid='.$postData['eid'];
        return $wechat_sign;
    }

    //验证是否链接传得openid是否合法验证
    public function check_wechat_sign($signature, $openid){
        $shopex_token = base_certificate::get('token');
        $tmpArr = array($shopex_token, $openid);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 将数组转换为xml,发送消息到微信
     *
     * @params array $data 发送消息结构数组
     */
    public function send($data=''){
        if( !empty($data) ){
            $xml = kernel::single('site_utility_xml')->array2xml($data,'xml');
        }else{
            $xml = '';
        }
        echo $xml;
    }

    //随机取6位字符数
    public function randomkeys($length){
        $key = '';
        $pattern = '1234567890';    //字符池
        for($i=0;$i<$length;$i++){
            $key .= $pattern{mt_rand(0,9)};    //生成php随机数
        }
        return $key;
    }

}
