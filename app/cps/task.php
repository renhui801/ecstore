<?php
class cps_task {

    public function post_install() {
        logger::info('Initial cps');
        kernel::single('base_initial', 'cps')->init();
        // $inst = kernel::single('cps_theme_inst');
        // $inst->instTheme();
    }
    
    public function post_uninstall() {
        $init = kernel::single('cps_init');
        logger::info('Uninstall cps');
        $inst = kernel::single('cps_theme_inst');
        $inst->uninstTheme();
    }
}