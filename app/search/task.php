<?php

class search_task
{

    public function pre_install()
    {
        app::get('search')->setConf('app_is_actived','true');
        logger::info('Initial search');
        kernel::single('base_initial', 'search')->init();
    }//End Function

    public function post_uninstall(){
        app::get('search')->setConf('app_is_actived','false');
    }

}//End Class
