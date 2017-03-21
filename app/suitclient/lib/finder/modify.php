<?php
    class suitclient_finder_modify
    {
        // 把添加删除用户功能屏蔽
        function action_modify(&$actions) {
            $actions= array(
                    array('label'=>app::get('suitclient')->_('同步管理员'),
                        'href'=>'index.php?app=suitclient&ctl=user&act=index',
                        #'target'=>'dialog::{title:\''.app::get('suitclient')->_('同步管理员').'    \'}'),
                        'target'=>'command::{title:\''.app::get('suitclient')->_('同步管理员').'    \'}'),
                    );
        }
    }
