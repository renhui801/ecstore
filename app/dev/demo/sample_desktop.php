<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class %*APP_NAME*%_ctl_%*CTL_DIR*%%*CTL_NAME*% extends desktop_controller{

    function %*FUNC_NAME*%(){
		$this->pagedata['app_name'] = "%*APP_NAME*%";
		$this->pagedata['testdata'] = "<h1>hello,控制器%*APP_NAME*%_ctl_%*CTL_DIR*%%*CTL_NAME*%!</h1>";
		$this->page('%*FUNC_NAME*%.html');
    }

}
