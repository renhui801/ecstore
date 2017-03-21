<?php

class weixin_util{

    /**
     *
     *
     * @param toURL
     * @param paras
     * @return
     */
    static function genAllUrl($toURL, $paras){
        $allUrl = null;
        if(null == $toURL){
            die("toURL is null");
        }
        if (strripos($toURL,"?") =="") {
            $allUrl = $toURL . "?" . $paras;
        }else {
            $allUrl = $toURL . "&" . $paras;
        }

        return $allUrl;
    }

    static function create_noncestr( $length = 16 ){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }

        return $str;
    }

    /**
     *
     *
     * @param src
     * @param token
     * @return
     */
    static function splitParaStr($src, $token){
        $resMap = array();
        $items = explode($token,$src);
        foreach ($items as $item){
            $paraAndValue = explode("=",$item);
            if ($paraAndValue != "") {
                $resMap[$paraAndValue[0]] = $parameterValue[1];
            }
        }

        return $resMap;
    }

    /**
     * trim
     * @param  string $value param
     * @return string        trim param
     */
    static function trimString($value){
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }
        return $ret;
    }

    /**
     * formatQueryParaMap
     * @param  array $paraMap   params
     * @param  bool $urlencode ifurlencode
     * @return string            url
     */
    static function formatQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
            if (null != $v && "null" != $v && "sign" != $k) {
                if($urlencode){
                   $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    static function formatBizQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
            if($urlencode){
               $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }

        return $reqPar;
    }

    static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
             if (is_numeric($val))
             {
                 $xml.="<".$key.">".$val."</".$key.">";

             }
             else
                 $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";

        return $xml;
    }

    static function sign($content, $key) {
        try {
            if (null == $key) {
               throw new Exception("财付通签名key不能为空！" . "<br>");
            }
            if (null == $content) {
               throw new Exception("财付通签名内容不能为空" . "<br>");
            }
            $signStr = $content . "&key=" . $key;

            return strtoupper(md5($signStr));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    static function verifySignature($content, $sign, $md5Key) {
        $signStr = $content . "&key=" . $md5Key;
        $calculateSign = strtolower(md5($signStr));
        $tenpaySign = strtolower($sign);
        return $calculateSign == $tenpaySign;
    }

    static function sign_sha1($data,$paySignKey){
        foreach ($data as $k => $v){
            $signData[strtolower($k)] = $v;
        }

        try {
            if($paySignKey == ""){
                throw new Exception("APPKEY为空！" . "<br>");
            }
            $signData["appkey"] = $paySignKey;
            ksort($signData);
            $signData = self::formatBizQueryParaMap($signData, false);
            return sha1($signData);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    static function verifySignatureShal($postData, $sign) {
        $payData = app::get('ectools')->getConf('weixin_payment_plugin_wxpay');
        $payData = unserialize($payData);
        $postData['appid'] = trim($payData['setting']['appId']);
        $paySignKey = trim($payData['setting']['paySignKey']); // 财付通商户权限密钥 Key

        $calculateSign = strtolower(self::sign_sha1($postData,self::trimString($paySignKey)));
        $tenpaySign = strtolower($sign);
        return $calculateSign == $tenpaySign;
    }

}
