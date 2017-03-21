<?php

class apiactionlog_panel {

    private $id;
    private $tmpl;
    private $controller;

    public function __construct(&$controller) {
        $this->controller = $controller;
    }

    public function show($object_name, $params) {
        $finder = kernel::single('apiactionlog_finder_builder_panel_filter',$this->controller);
       // $finder = new desktop_finder_builder_panel_filter($this->controller);

        foreach ($params as $k => $v) {
            $finder->$k = $v;
        }

        $app_id = substr($object_name, 0, strpos($object_name, '_'));
        $app = app::get($app_id);

        $finder->app = $app;
        $finder->setId($this->id);
        $finder->setFile($this->tmpl);

        $finder->work($object_name);
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTmpl($tmpl) {
        $this->tmpl = $tmpl;
    }

}
