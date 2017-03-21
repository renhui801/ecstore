<?php
/**
 * cps_thirdparty_track
 * 第三方CPS接口类
 * 
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_thirdparty_track Jul 29, 2011  3:25:06 PM ever $
 */
class cps_thirdparty_track {
    
    /**
     * 第三方CPS接口
     * @access public
     * @param array $params get传递参数
     * @version 1 Jul 29, 2011
     */
    public function enter($params) {
        //根据请求源进行处理
        switch ($params['src']) {
            case 'emar':
                $this->emar($params);
                break;
            default:
                header('Location: ' . kernel::base_url());
                break;
        }
        
        //页面跳转到指定页面
        $url = $params['url']? base64_decode($params['url']) : kernel::base_url();
        header('Location: ' . $url);
    }
    
    /**
     * 设置亿起发参数
     * @access private
     * @param array $params get传递参数
     * @version 1 Jul 29, 2011
     */
    private function emar($params) {
        $url = $_SERVER['HTTP_REFERER'];
        //从表中查询cookie有效期，单位：天
        $duration = app::get('cps')->model('setting')->getValueByKey('emarCookie');
        $duration = time() + ($duration? $duration * 86400 : 2592000);
        //来源数组
        $refer = array(
            'ID' => 'emar',
            'REFER' => $url,
            'DATE' => time() * 1000,
            'cid' => $params['cid'],
            'wi' => $params['wi'],
        );
        //来源写入Cookie
        $this->setRefer($refer, $duration);
    }
    
    /**
     * 将来源写入Cookie
     * @access private
     * @param array $refer 订单来源
     * @param int $duration cookie有效期
     * @version 1 Aug 1, 2011
     */
    private function setRefer($refer, $duration) {
        //判断首次来源不存在，首次来源写入Cookie
        if (empty($_COOKIE['S']['FIRST_REFER'])) {
            //判断当前来源存在，首次来源写入Cookie
            if ($_COOKIE['S']['NOW_REFER']) {
                setcookie('S[FIRST_REFER]', $_COOKIE['S']['NOW_REFER'], $duration, '/');
            } else {
                setcookie('S[FIRST_REFER]', json_encode($refer), $duration, '/');
            }
        }
        //当前来源写入Cookie
        setcookie('S[NOW_REFER]', json_encode($refer), $duration, '/');
    }
}