<?php
/**
 * cps_info_detail
 * 文章相关第三方类，KVStore相关
 *
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_info_detail Jun 23, 2011  8:38:24 PM ever $
 */
class cps_info_detail {

    /**
     * 构造方法
     * @access public
     * @version 1 Jun 24, 2011 创建
     */
    public function __construct() {
    }

    /**
     * 删除具体文章kvstore缓存内容
     * @access public
     * @param unknown_type $infoId
     * @return bool
     * @version 1 Jun 24, 2011 创建
     */
    public function delete_info_kvstore($infoId) {
        //删除具体文章缓存
        $rs = base_kvstore::instance('cache/cps/info')->delete('info_' . $infoId);

        //设置缓存更新时间
        if ($rs) {
            $this->store_info_change();
        }

        return $rs;
    }

    /**
     * 获取setting中保存的文章的最新修改更新时间
     * @access public
     * @return int
     * @version 1 Jun 24, 2011 创建
     */
    public function fetch_info_change() {
        //获取setting中保存的文章的最新修改更新时间
        return app::get('cps')->getConf('cps.kvstore_info_change');
    }

    /**
     * 获取具体文章kvstore缓存内容
     * @access public
     * @param int $infoId 文章id
     * @param array &$value 文章内容
     * @version 1 Jun 24, 2011 创建
     */
    public function fetch_info_kvstore($infoId, &$value) {
        //获取具体文章缓存内容
        return base_kvstore::instance('cache/cps/info')->fetch('info_' . $infoId, $value);
    }

    /**
     * 获取具体文章内容(判断缓存是否过期，是否直接读取数据库)
     * @access public
     * @param int $infoId 文章id
     * @param bool $kvstore 是否调用kvstore
     * @return array
     * @version 1 Jun 24, 2011 创建
     */
    public function get_detail($infoId, $kvstore = false) {
        //是否采用缓存
        if($kvstore == true) {
            //获取缓存
            if ($this->fetch_info_kvstore($infoId, $value) == true) {
                //获取缓存更新时间
                $this->fetch_info_change();
            } else {
                //从数据库中获取数据
                $value = app::get('cps')->model('info')->getInfoById($infoId);

                //更新缓存
                $this->store_info_kvstore($infoId, $value);
            }
        }

        return $value;
    }

    /**
     * 设置setting中保存的文章的最新修改更新时间
     * @access public
     * @return bool
     * @version 1 Jun 24, 2011 创建
     */
    public function store_info_change() {
        //设置setting中保存的文章的最新修改更新时间
        return app::get('cps')->setConf('cps.kvstore_info_change', time());
    }

    /**
     * 保存具体文章kvstore缓存内容
     * @access public
     * @param int $infoId 文章id
     * @param array ＆$value 文章内容
     * @return bool
     * @version 1 Jun 24, 2011 创建
     */
    public function store_info_kvstore($infoId, &$value) {
        //保存具体文章缓存内容
        $rs = base_kvstore::instance('cache/cps/info')->store('info_' . $infoId, $value);

        //设置缓存更新时间
        if ($rs) {
            $this->store_info_change();
        }

        return $rs;
    }
}