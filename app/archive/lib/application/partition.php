<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class archive_application_partition extends base_application_prototype_xml{

    var $xml='partition.xml';
    var $xsd='archive_partition';
    var $path = 'partition';

    /**
    * 迭代找到当前类实例
    * @return object 返回当前类实例
    */
    public function current(){
        $this->current = $this->iterator()->current();
        return $this;
    }

    /**
    * 安装资源数据,如果有数据不是先删除再更新
    * @access final
    */
    final public function install(){
        $filter = array(
            'app'=>$this->target_app->app_id,
            'table'=>$this->current['table'],
        );
        $row = app::get('archive')->model('partition')->getRow('*', $filter);
        if($row['id']){
            $data = $this->row();
            $data['id'] = $row['id'];
            $flag = app::get('archive')->model('partition')->save($data);
            if($flag && $row['method']=='hash' && $row['nums']!=$data['nums']){
                $this->update_hash_partition($data);
            }
            return $row['id'];
        }else{
            $data = $this->row();
            $flag = app::get('archive')->model('partition')->insert($data);
            if($flag && $data['method']=='hash'){
                $this->update_hash_partition($data);
            }
            return $flag;
        }
    }

    function row(){
        $row['app']    = $this->target_app->app_id;
        $row['table']  = $this->current['table'];
        $row['method'] = $this->current['method'];
        $row['nums']   = $this->current['nums'];
        $row['expr']   = $this->current['expr'];
        $row['last']   = time();
        return $row;
    }

    /**
    * 卸载资源数据 partition 表中对应APP的数据删除
    * @param string $app_id appid
    */
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        app::get('archive')->model('partition')->delete(array('app'=>$app_id));
    }

    function update_hash_partition($data){
        $table_name = kernel::database()->prefix.$data['app'].'_'.$data['table'];
        kernel::single('archive_tasks_partition')->hash($data, $table_name);
    }
}
