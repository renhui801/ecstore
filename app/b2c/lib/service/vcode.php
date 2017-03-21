<?php
class b2c_service_vcode{

    public function __construct($app){
        $this->app = $app;
    }

    public function status(){
        if(app::get('b2c')->getConf('site.login_valide') == 'false'){
            if($_SESSION['error_count']['b2c']['count'] >= 3){
                return 1;
            }else{
                return 0;
            } 
        }
        return app::get('b2c')->getConf('site.login_valide') == 'true' ? 1 : 0;
    }

    public function set_error_count(){
      if(isset($_SESSION['error_count']['b2c']['time']) && (time() - $_SESSION['error_count']['b2c']['time']<3600) ){
          $_SESSION['error_count']['b2c']['count'] += 1;
      }else{
          $_SESSION['error_count']['b2c']['time'] = time();
          $_SESSION['error_count']['b2c']['count'] = 1;
      }
    }
}
?>
