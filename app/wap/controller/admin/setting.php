<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class wap_ctl_admin_setting extends desktop_controller{

    var $workground = 'wap.workground.theme';

    public function __construct($app){
        parent::__construct($app);
        $this->ui = new base_component_ui($this);
        $this->app = $app;
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    public function index(){
        $this->basic();
    }

    function basic(){
        $all_settings = array(
            app::get('wap')->_('基本设置')=>array(
                'wap.logo',
                'wap.apple.desktop',
                'wap.shopname',
                'wap.status',
                'wap.scanbuy',
                'wap.register.license',
                'wap.foot_edit',
            ),
        );

        $html= $this->_process($all_settings);
        echo $html;
    }

    function _process($all_settings){
        $setting = new base_setting($this->app);
        $setlib = $setting->source();
        $typemap = array(
            SET_T_STR=>'text',
            SET_T_INT=>'number',
            SET_T_ENUM=>'select',
            SET_T_BOOL=>'bool',
            SET_T_TXT=>'html',
            SET_T_FILE=>'file',
            SET_T_IMAGE=>'image',
            SET_T_DIGITS=>'number',
        );
        $tabs = array_keys($all_settings);
        $html = $this->ui->form_start(array('tabs'=>$tabs,'method'=>'POST'));
        $input_style = false;
        $arr_js = array();
        foreach($tabs as $tab=>$tab_name){
            foreach($all_settings[$tab_name] as $set){
                $current_set = $this->app->getConf($set);
                if($set == 'wap.shopname'){
                    $current_set = app::get('wap')->getConf('wap.name');
                }
                if($_POST['set'] && array_key_exists($set,$_POST['set'])){
                    if($current_set!==$_POST['set'][$set]){
                        $current_set = $_POST['set'][$set];
                        $this->app->setConf($set,$_POST['set'][$set]);
                    }
                }

                $input_type = $typemap[$setlib[$set]['type']];

                $form_input = array(
                    'title'=>$setlib[$set]['desc'],
                    'type'=>$input_type,
                    'name'=>"set[".$set."]",
                    'tab'=>$tab,
                    'helpinfo'=>$setlib[$set]['helpinfo'],
                    'value'=>$current_set,
                    'options'=>$setlib[$set]['options'],
                    'vtype' => $setlib[$set]['vtype'],
                    'class' => $setlib[$set]['class'],
                    'id' => $setlib[$set]['id'],
                    'default' => $setlib[$set]['default'],
                );
                if ($input_type=='select')
                    $form_input['required'] = true;

                if (isset($setlib[$set]['extends_attr']) && $setlib[$set]['extends_attr'] && is_array($setlib[$set]['extends_attr']))
                {
                    foreach ($setlib[$set]['extends_attr'] as $_key=>$extends_attr)
                    {
                        $form_input[$_key] = $extends_attr;
                    }
                }

                $arr_js[] = $setlib[$set]['javascript'];

                $html.=$this->ui->form_input($form_input);
            }
        }

        if (!$_POST)
        {
            $this->pagedata['_PAGE_CONTENT'] = $html .= $this->ui->form_end();

            $str_js = '';
            if (is_array($arr_js) && $arr_js)
            {
                foreach ($arr_js as $str_javascript)
                {
                    $str_js .= $str_javascript;
                }
            }

            if ($str_js)
            {
                $this->pagedata['_PAGE_CONTENT'] .= '<script type="text/javascript">window.addEvent(\'domready\',function(){';
                $this->pagedata['_PAGE_CONTENT'] .= $str_js . '});</script>';
            }

            $this->page();
        }
        else
        {
            $this->begin();
            app::get('wap')->setConf('wap.name',$_POST['set']['wap.shopname']);
            $this->end(true, app::get('wap')->_('当前配置修改成功！'));
        }
    }

    function imageset(){
        $ctl = new image_ctl_admin_manage($this->app);
        $ctl->imageset();
    }
}

