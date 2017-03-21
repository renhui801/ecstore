<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
/**
* account model类
*/
class pam_mdl_members extends dbeav_model{
	/**
	* 关联MODEL
	* @var array 
	*/
	var $has_many = array(
        'account'=>'auth:append:member_id^account_id',
    );
	/**
	* dump 等操作的相关联表
	* @var array 
	*/
var $subSdf = array(
        'delete' => array(
            'account:auth' => array('*'),
         )
    );
	
	/**
	 * 得到帐号用户名
	 * @param int $account_id 用户ID
	 * @return string 返回ID对应的用户名
	 */
	public function get_operactor_name($member_id='')
	{
		if ($member_id == '')
			return app::get('pam')->_('未知或无操作员');
		
    $data = $this->getList('login_type,login_account',array('member_id'=>$member_id));
		if (!$data)
		{
			return app::get('pam')->_('未知或无操作员');
		}

    foreach((array)$data as $row){
      $arr_name[$row['login_type']] = $row['login_account'];
    }

    if( isset($arr_name['local']) ){
      $login_name = $arr_name['local'];
    }elseif(isset($arr_name['email'])){
      $login_name = $arr_name['email'];
    }else{
      $login_name = $arr_name['mobile'];
    }
    return $login_name;
	}
}
