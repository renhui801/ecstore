<?php
    class suitclient_ctl_user extends desktop_controller
    {
        function __construct($app) {
            $this->app = $app;
        }
        function index() {
            kernel::single('base_shell_webproxy')->exec_command('suitclient:sync sync_user');
        }

    }
