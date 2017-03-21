<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_member_addrs extends dbeav_model{

    /* member address number limit */
    public $addrLimit = 10;

    function save(&$data,$mustUpdate=null,$mustInsert = false){
        if($data['area'])
        $data['area'] = $data['area']['area_type'].':'.implode('/',$data['area']['sar']).':'.$data['area']['id'];
		$info_object = kernel::service('sensitive_information');
		if(is_object($info_object)) $info_object->opinfo($data,'b2c_mdl_member_addrs',__FUNCTION__);
        return parent::save($data,$mustUpdate);
    }

    public function set_default_addr($arr_data=array(), $addr_id=0, $member_id=0, &$msg='')
    {
        $filter = array('member_id' => $member_id);
        $cnt = $this->count($filter);
        if ($cnt < $this->addrLimit && $arr_data['def_addr']){
            $arr_update['def_addr'] = 0;
            $is_updated = $this->update($arr_update, $filter);
        }

        if ($addr_id){
            $is_updated = $this->update($arr_data, array('addr_id' => $addr_id));
        }else{
            if ($cnt < $this->addrLimit){
                $is_updated = $this->insert($arr_data);
            }else{
				$msg = app::get('b2c')->_('最多只能添加10个地址，请先删除一条地址之后再添加');
                $is_updated = false;
			}
        }
        return $is_updated;
    }

    public function is_exists_addr($data=array(),$member_id=null)
    {
        if(!$data || !$member_id) return false;
        if($data['area'])
        $data['area'] = $data['area']['area_type'].':'.implode('/',$data['area']['sar']).':'.$data['area']['id'];
        $row = $this->getList('addr_id',array('member_id' => $member_id,'name' => $data['name'],'area' => $data['area'],'addr' => $data['addr']));
        if(!$row)
            return false;
        else{
            if($data['addr_id'] == $row[0]['addr_id'])
                return false;
            else
                return true;
        }

    }

		/**
     * 重写getList方法
     * @param string column
     * @param array filter
     * @param int offset
     * @param int limit
     * @param string order by
     */
	public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
	{
		$addrs_member = parent::getList($cols, $filter, $offset, $limit, $orderType);
		$info_object = kernel::service('sensitive_information');
		if(is_object($info_object)) $info_object->opinfo($addrs_member,'b2c_mdl_member_addrs',__FUNCTION__);
		return $addrs_member;
	}
}
