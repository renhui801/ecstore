<?php

class searchrule_task
{

    public function pre_install()
    {
        app::get('searchrule')->setConf('app_is_actived','true');
        logger::info('Initial searchrule');
        kernel::single('base_initial', 'searchrule')->init();
    }//End Function

    public function post_uninstall(){
        app::get('searchrule')->setConf('app_is_actived','false');
    }

}//End Class
