<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class wap_mdl_themes extends dbeav_model
{

    public $defaultOrder = array('is_used', 'asc');
       
    public function delete_file($filter) 
    {
        $rows = $this->getList('*',$filter);
        foreach($rows AS $row){
            if($row['theme'] == kernel::single('wap_theme_base')->get_default()){
                trigger_error(app::get('wap')->_("默认模板不能删除，请重新选择。"), E_USER_ERROR);
                return false;
            }
        }
        foreach($rows AS $row){
            kernel::single('wap_theme_install')->remove_theme($row['theme']);
        }
        return true;
    }//End Function

}//End Class
