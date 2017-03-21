<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */
class proregister_setting
{
    private $_name = 'promotion';
    private $_setting;
    private $_status;
    
    function __construct( &$app )
    {
        $this->app = $app;
        $this->_status = array( '1'=>'是','2'=>'否' );
    }
    /**
     * 获取配置信息
     * 
     * @return array
     */ 
    public function getSetting() {
        $this->_setting = $this->app->getConf( $this->_name );
        return $this->_setting;
    }

    /**
     * 设置配置信息
     *
     * @param array $arr 选项
     * @return array
     */ 
    public function setSetting( $arr ) {
        $arr['stime'] = strtotime( $arr['stime'] );
        $arr['etime'] = strtotime( $arr['etime'] );
        $this->app->setConf( $this->_name,$arr );
        $this->_setting = $arr;
        return $this->_setting;
    }
    /**
     * 获取启用状态
     *
     * @return array
     */
    public function getStatusArr() {
        return $this->_status;
    }
    
    
    /**
     * 验证活动状态
     *
     * @return boolean
     */
    public function checkStatus()
    {
        if( !$this->_setting ) {
            $this->getSetting();
        }
        
        return ($this->_setting['status']=='2') ? false : true;
    }
    #End Func
}
