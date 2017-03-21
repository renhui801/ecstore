<?php
/**
 * cps_mdl_info
 * 网站联盟消息模型
 *
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_info Jun 20, 2011  2:45:33 PM ever $
 */
class cps_mdl_info extends dbeav_model {
    public $defaultOrder = 'info_id DESC';

    /**
     * 获取相关文章的数量统计
     * @access public
     * @see dbeav_model::count()
     * @param array $filter 过滤条件
     * @return int
     * @version 2 Jun 24, 2011 修改
     */
    public function count($filter = null) {
        return parent::count($filter);
    }

    /**
     * 保存新增消息数据，更新当前kvstore
     * @access public
     * @see dbeav_model::insert()
     * @param array $data 插入数据
     * @return int
     * @version 2 Jun 24, 2011 修改逻辑
     */
    public function insert(&$data) {
        //检验数据
        $data = $this->valid_insert($data);

        //数据不为空
        if (empty($data)) {
            return false;
        }

        //调用父类插入数据
        $insert_id = parent::insert($data);

        //插入成功更新缓存
        if ($insert_id) {
            //获取插入数据
            $arrInfo = $this->getInfoById($insert_id);
            //更新缓存
            kernel::single('cps_info_detail')->store_info_kvstore($insert_id, $arrInfo);
            return $insert_id;
        }else{
            return false;
        }
    }

    /**
     * 更新消息数据，更新当前kvstore
     * @access public
     * @see dbeav_model::update()
     * @param array $data 更新数据
     * @param array $filter 过滤条件
     * @param bool $mustUpdate 更新条件
     * @return bool
     * @version 2 Jun 24, 2011 修改功能逻辑
     */
    public function update($data, $filter=array(), $mustUpdate = null){
        //检查数据
        $data = $this->valid_update($data);

        //数据不为空
        if (empty($data)) {
            return false;
        }

        //调用父类更新数据，然后进行缓存更新
        if (parent::update($data, $filter, $mustUpdate)) {
            //获取所有更新数据
            $rows = $this->getList('*', $filter);

            //更新缓存
            foreach ($rows as $row) {
                //调用缓存更新
                kernel::single('cps_info_detail')->store_info_kvstore($row['info_id'], $row);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除消息数据，更新当前kvstore
     * @access public
     * @see dbeav_model::delete()
     * @param array $filter 过滤条件
     * @param string $subSdf
     * @return bool
     * @version 2 Jun 24, 2011 修改功能逻辑
     */
    public function delete($filter, $subSdf = 'delete'){
        //获取删除消息的id
        $rows = $this->getList('info_id', $filter);

        //删除消息，删除缓存
        if(parent::delete($filter, $subSdf)){
            //删除缓存
            foreach($rows as $row){
                kernel::single('cps_info_detail')->delete_info_kvstore($row['info_id']);
            }
             kernel::single('cps_info_detail')->store_info_change();
            return true;
        }else{
            return false;
        }
    }

    /**
     * 对于保存数据中的数据类型的格式化，主要进行SEO格式化，留二期
     * @access public
     * @param array $params 需处理数据
     * @return array
     * @version 2 Jun 24, 2011 修改
     */
    public function format_params($params) {
        return $params;
    }

    /**
     * 列表项查询条件定义，如按文章标题查询
     * @access public
     * @see dbeav_model::searchOptions()
     */
    public function searchOptions() {
        return parent::searchOptions();
    }

    /**
     * 消息数据插入验证
     * @access public
     * @param array $params 处理数据
     * @return int
     * @version 1 Jun 23, 2011 创建，复制参考方法
     */
    public function valid_insert($params) {
        $params = $this->format_params($params);
        return $params;
    }

    /**
     * 消息数据更新验证
     * @access public
     * @param array $params 处理数据
     * @return array
     * @version 1 Jun 23, 2011 创建，复制参考方法
     */
    public function valid_update($params) {
        $params = $this->format_params($params);
        return $params;
    }

    /**
     * 获取相关文章的全部信息
     * @access public
     * @param array $aField 获取的字段
     * @return array
     */
    public function getInfoList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null) {
        //设置默认排序
        if( !$orderType ) $orderType = 'info_id ASC';
        return parent::getList($cols, $filter, $offset, $limit, $orderType);
    }

    /**
     * 根据具体id获取一个文章的相关信息
     * @access public
     * @param int $infoId 文章id
     * @param array $aField 获取的字段
     * @return array
     * @version 1 Jun 22, 2011 创建
     */
    public function getInfoById($infoId, $aField = array('*')) {
        //组装需要获取的字段
        $strCols = implode(',', $aField);
        //根据具体id获取一个文章的相关信息
        $arrInfo = $this->dump($infoId, $strCols);
        return $arrInfo;
    }

    /**
     * 获取文章类型的数组
     * @access public
     * @return array
     * @version 1 Jun 22, 2011 创建
     */
    public function getInfoType() {
        //获取所有文章类型
        $arrInfoTypes = $this->schema['columns']['i_type']['type'];
        return $arrInfoTypes;
    }
}
