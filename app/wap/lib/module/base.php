<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class wap_module_base
{      
    final public function create_site_config() 
    {
        $conf = $this->assemble_config();
        return $this->write_config($conf);
    }//End Function

    final public function assemble_config() 
    {
        $rows = app::get('wap')->model('modules')->select()->where('enable = ?', 'true')->instance()->fetch_all();
        if(is_array($rows)){
            $conf = array();
            foreach($rows AS $row){
                $conf[$row['path']] = array($row['app'], $row['ctl'], $row['title'], trim($row['extension']), 
                                            'use_ssl' => $row['use_ssl'] === 'true' ? true : false);
            }
            return $conf;
        }
        return false;
    }//End Function

    final public function write_config($conf) 
    {
        if(is_array($conf)){
            return app::get('wap')->setConf('sitemaps', $conf);
        }else{
            return false;
        }
    }//End Function

}//End Class
