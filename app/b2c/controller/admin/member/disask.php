<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_member_disask extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function basic_setting(){
        $member_comments = kernel::single('b2c_message_disask');
        $aOut = $member_comments->get_basic_setting();
        $this->pagedata['setting']= $aOut;
        if($_POST['base_setting'] == 'true'){
            $this->pagedata['base_setting'] = 'true';
             echo $this->fetch('admin/member/basic_setting.html');
        }
        else $this->page('admin/member/basic_setting.html');
    }

    function to_setting(){
        $this->begin();
        $member_comments = kernel::single('b2c_message_disask');
        $aOut = $member_comments->save_basic_setting($_POST);
        $this->end('success',app::get('b2c')->_('设置成功'));
    }
}
