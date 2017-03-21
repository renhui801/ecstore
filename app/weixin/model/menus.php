<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

/**
 * @package weixin
 * @subpackage dbeav_model
 * @copyright Copyright (c) 2010, shopex. inc
 * @author edwin.lzh@gmail.com
 * @license 
 */
class weixin_mdl_menus extends dbeav_model
{

    /**
     * 验证插入数据
     * @var array $params
     * @access public
     * @return array
     */
    public function valid_insert($params) 
    {
        if(empty($params['menu_name'])){
            trigger_error(app::get('weixin')->_('菜单名称不能为空'), E_USER_ERROR);
            return false;
        }
        $params['parent_id'] = ($params['parent_id'] > 0) ? $params['parent_id'] : 0;
        $params['ordernum'] = ($params['ordernum'] > 0) ? $params['ordernum'] : 0;
        return $params;
    }//End Function


    /**
     * 添加节点
     * @var array $params
     * @access public
     * @return boolean
     */
    public function insert($params)
    {
        $params = $this->valid_insert($params);
        if(!$params)    return false;
        $insert_id = parent::insert($params);
        if($insert_id){
            $this->upgrade_parent($insert_id);
            return $this->update_menu_path($insert_id);   //更新菜单路径信息
        }else{
            return false;
        }
    }

    /**
     * 更新菜单
     * @var int $menu_id
     * @var array $params
     * @access public
     * @return boolean
     */
    public function update($params, $filter)
    {
        if(!$params)    return false;
        $rows = $this->getList('*', $filter);
        if(isset($params['parent_id'])){
            $menu_path_arr = array();
            if($params['parent_id'] > 0){
                $menu_path = $this->select()->columns('menu_path')->where('menu_id = ?', $params['parent_id'])->instance()->fetch_one();
                if($menu_path){
                    $menu_path_arr = @explode(",", $menu_path);
                }
            }
            foreach($rows AS $row){
                if($row['menu_id'] == $params['parent_id']){
                    trigger_error(app::get('weixin')->_('菜单『').$row['menu_name'].app::get('weixin')->_("』的父菜单不能为自己"), E_USER_ERROR);
                    return false;   //父菜单不能更新为自己，防止错误
                }
                if(in_array($row['menu_id'], $menu_path_arr)){
                    trigger_error(app::get('weixin')->_('菜单『').$row['menu_name'].app::get('weixin')->_("』的父菜单不能为自己的子节点"), E_USER_ERROR);
                    return false;   //父菜单不能移动至自己的子菜单，防止错误
                }
            }
        }
        $res = parent::update($params, $filter);
        if($res){
            foreach($rows AS $row){
                if(isset($params['parent_id']) && $row['parent_id'] != $params['parent_id']){
                    $this->upgrade_parent($row['menu_id']);
                    $this->update_menus_path($row['menu_id']);
                    $this->update_menu_path($row['parent_id']);
                }
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * 移除菜单
     * @var int $menu_id
     * @access public
     */
    public function delete($filter,$subSdf = 'delete')
    {
        $rows = $this->getList('*', $filter);
        foreach($rows AS $row){
            if($this->has_chilren($row['menu_id'])){
                trigger_error(app::get('weixin')->_("菜单『").$row['menu_name'].app::get('weixin')->_("』下存在子菜单，不能删除"), E_USER_ERROR);
                return false;   //存在子菜单
            }
        }
        $res = parent::delete($filter);
        if($res){
            foreach($rows AS $row){
                if($row['parent_id'] > 0){
                    $this->update_menu_path($row['parent_id']);
                }
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * 更新菜单下所有菜单的菜单信息包括自身
     * @var int $menu_id
     * @access public
     * @return void
     */
    public function update_menus_path($menu_id) 
    {
        if(empty($menu_id))    return false;
        $menu_id = intval($menu_id);
        $this->update_menu_path($menu_id);
        $rows = $this->select()->columns('menu_id')->where('parent_id = ?', $menu_id)->instance()->fetch_all();
        foreach($rows AS $data){
            $this->update_menus_path($data['menu_id']);
        }
    }//End Function


    /**
     * 更新菜单path信息
     * @var int $menu_id
     * @access public
     * @return boolean
     */
    public function update_menu_path($menu_id) 
    {
        if(empty($menu_id)) return false;
        $params = $this->get_menu_path($menu_id);
        $params['has_children'] = ($this->has_chilren($menu_id)) ? 'true' : 'false';
        return $this->update($params, array('menu_id'=>intval($menu_id)));
    }//End Function

    /**
     * 取得菜单path信息
     * @var int $menu_id
     * @access public
     * @return array
     */
    public function get_menu_path($menu_id) 
    {
        if(empty($menu_id)) false;
        $menu_id = intval($menu_id);
        $row = $this->select()->where('menu_id = ?', $menu_id)->instance()->fetch_row();
        if($row['parent_id'] == 0)  return array('menu_depth'=>1, 'menu_path'=>$row['menu_id']);
        $parentRow = $this->select()->where('menu_id = ?', $row['parent_id'])->instance()->fetch_row();
        $path = $parentRow['menu_path'] . ',' . $row['menu_id'];
        return array('menu_depth'=>count(explode(',', $path)), 'menu_path'=>$path);
    }//End Function

    /**
     * 强制检测是否有子菜单
     * @var int $menu_id
     * @access public
     * @return boolean
     */
    public function has_chilren($menu_id) 
    {
        if(empty($menu_id)) return false;
        $menu_id = intval($menu_id);
        $count = $this->select()->columns('count(*)')->where('parent_id = ?', $menu_id)->instance()->fetch_one();
        if($count){
            return $count;
        }else{
            return false;
        }
    }//End Function

    /**
     * 更新菜单父类
     * @var int $menu_id
     * @access public
     * @return boolean
     */
    public function upgrade_parent($menu_id) 
    {
        $menu_id = intval($menu_id);
        $parent_id = $this->select()->columns('parent_id')->where('menu_id = ?', $menu_id)->instance()->fetch_one();
        if($parent_id > 0){
            return $this->update_menu_path($parent_id);
        }
        return true;
    }//End Function

    /**
     * 取得菜单信息
     * @var int $menu_id
     * @access public
     * @return boolean
     */
    public function get_by_id($menu_id) 
    {
        $menu_id = intval($menu_id);
        return $this->select()->where('menu_id = ?', $menu_id)->instance()->fetch_row();
    }//End Function
    /*
     * 取得子菜单
     * @var int $parent_id 父菜单I
     * @access publi
     * @return arra
     */
    public function get_childrens_id($parent_id) 
    {
        $parent_id = intval($parent_id);
        if($parent_id > 0){
            $data =  $this->select()->columns('menu_id')->where('FIND_IN_SET("'.$parent_id.'", menu_path)')->instance()->fetch_col();
        }else{
            $data = $this->select()->columns('menu_id')->instance()->fetch_col();
        }
        return $data['menu_id'];
    }//End Function
}//End Class
