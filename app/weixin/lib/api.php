<?php

class weixin_api{

    public function __construct($app){
        $this->wechat = kernel::single('weixin_wechat');
        $this->weixinObject = kernel::single('weixin_object');
    }

    public function api(){
        //签名验证，消息有效性验证
        if( !empty($_GET) && $this->doget() ){
            echo $_GET["echostr"];
        }

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if( !empty($postStr) ){
            $this->dopost($postStr);
        }else{
            echo "";
        }
    }

    /**
     * 处理微信消息有效验证
     */
    public function doget(){
        //获取到token
        $token = $this->weixinObject->get_token($_GET['eid']);
        //验证
        if( $this->wechat->checkSignature($_GET["signature"], $_GET["timestamp"], $_GET["nonce"], $token) ){
            return true;
        }else{
            return false;
        }
    }

    public function dopost($postXml){
        $postArray = kernel::single('site_utility_xml')->xml2array($postXml);
        $postData  = $postArray['xml'];

        //公众账号ID获取
        $weixin_id = $postData['ToUserName'];
        $bind = app::get('weixin')->model('bind')->getList('id,eid',array('weixin_id'=>$weixin_id,'status'=>'active'));
        if( !empty($bind) ){
            $postData['bind_id'] = $bind[0]['id'];
            $postData['eid'] = $bind[0]['eid'];
        }else{
            $this->weixinObject->send('');
        }

        switch($postData['MsgType']){
        case 'event':
            /**
             * subscribe(订阅)、unsubscribe(取消订阅)
             * scan 带参数二维码事件
             * location 上报地理位置事件
             * click 自定义菜单事件
             * view  点击菜单跳转链接时的事件推送
             * */
            $method = strtolower($postData['Event']);
            if( method_exists($this->wechat,$method) ){
                $this->wechat->$method($postData);
            }else{
                $this->weixinObject->send('');
            }
            break;
        default:
            $this->wechat->commonMsg($postData);
        }
    }

    // 微信支付回调地址
    function wxpay(){
        $postData = array();
        $httpclient = kernel::single('base_httpclient');
        $callback_url = kernel::openapi_url('openapi.ectools_payment/parse/weixin/weixin_payment_plugin_wxpay', 'callback');

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData['weixin_postdata']  = $postArray['xml'];

        $nodify_data = array_merge($_GET,$postData);
        $response = $httpclient->post($callback_url, $nodify_data);
        echo 'success';exit;
    }

    // 维权通知接口
    public function safeguard(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData  = $postArray['xml'];
        #$postData = array (
        #    'OpenId' => 'ow1l7t6coRbI3aBBNztBc6qT8F4w',
        #    'AppId' => 'wxfdd2db839d9e8984',
        #    'TimeStamp' => '1403080919',
        #    'MsgType' => 'request',
        #    'FeedBackId' => '13221259825330037179',
        #    'TransId' => '1219419901201406183166617972',
        #    'Reason' => '商品质量有问题',
        #    'Solution' => '退款，并不退货',
        #    'ExtInfo' => '我是备注 1391000000',
        #    'AppSignature' => '5f0dba6a6ba427cf523f22c815f6600cfbe1c365',
        #    'SignMethod' => 'sha1',
        #);
        $signData = array(
            'OpenId' => $postData['OpenId'],
            'TimeStamp' => $postData['TimeStamp'],
        );
        if(!weixin_util::verifySignatureShal($signData, $postData['AppSignature'])){
            return false;
        }
        
        $saveData['openid'] = $postData['OpenId'];
        $saveData['appid'] = $postData['AppId'];
        $saveData['msgtype'] = $postData['MsgType'];
        $saveData['feedbackid'] = $postData['FeedBackId'];
        $saveData['transid'] = $postData['TransId'];
        $saveData['reason'] = $postData['Reason'];
        $saveData['solution'] = $postData['Solution'];
        $saveData['extinfo'] = $postData['ExtInfo'];
        $saveData['picurl'] = $postData['PicUrl'];
        $saveData['timestamp'] = $postData['TimeStamp'];
        $safeguardModel = app::get('weixin')->model('safeguard');
        $row = $safeguardModel->getRow('id',array('feedbackid'=>$saveData['feedbackid']));
        if( $row ){
            if( $saveData['msgtype'] == 'confirm'){
                $status = '3';
                $safeguardModel->update(array('msgtype'=>$saveData['msgtype'],'status'=>$status),array('id'=>$row['id']));
            }else{
                $saveData['status'] = '1';
                $safeguardModel->update($saveData,array('id'=>$row['id']));
            }
        }else{
            $bindData = app::get('weixin')->model('bind')->getRow('id',array('appid'=>$saveData['appid'])); 
            $res = kernel::single('weixin_wechat')->get_basic_userinfo($bindData['id'],$saveData['openid']);
            $saveData['weixin_nickname'] = $res['nickname'];
            if( !$safeguardModel->save($saveData) ){
                logger::info(var_export($saveData,1),'维权信息记录失败');
            }
        }
    }

    // 微信告警通知接口
    public function alert(){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData  = $postArray['xml'];

        $insertData = array(
            'appid' =>$postData['AppId'],
            'errortype' => $postData['ErrorType'],
            'description' => $postData['Description'],
            'alarmcontent' => $postData['AlarmContent'],
            'timestamp' => $postData['TimeStamp'],
        );
        app::get('weixin')->model('alert')->save($insertData);
        echo 'success';exit;
        /*告警通知数据格式
         $postData=array(
            'AppId'=>'wxf8b4f85f3a794e77',
            'ErrorType'=>'1001',
            'Description'=>'错误描述',
            'AlarmContent'=>'错误详情',
            'TimeStamp'=>'1393860740',
            'AppSignature'=>'f8164781a303f4d5a944a2dfc68411a8c7e4fbea',
            'SignMethod'=>'sha1'
        );*/
    }

}
