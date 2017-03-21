<?php
    class suitclient_user_sync
    {
        //套件单个推过来的
        function sync() {
            $api = new suitclient_api();
            $name = $_POST['name'];
            $api->add_user($name);
        }
    }
