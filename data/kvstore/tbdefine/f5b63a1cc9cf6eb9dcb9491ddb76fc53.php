<?php exit(); ?>a:3:{s:5:"value";a:10:{s:7:"columns";a:12:{s:2:"id";a:5:{s:5:"label";s:6:"序号";s:4:"type";s:11:"varchar(32)";s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:11:"varchar(32)";}s:10:"process_id";a:5:{s:5:"label";s:12:"进程序号";s:4:"type";s:11:"varchar(32)";s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:11:"varchar(32)";}s:4:"type";a:5:{s:4:"type";a:2:{s:7:"request";s:12:"发出请求";s:8:"response";s:15:"接收的请求";}s:5:"label";s:6:"类型";s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:26:"enum('request','response')";}s:8:"calltime";a:5:{s:4:"type";s:4:"time";s:5:"label";s:24:"请求或被请求时间";s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:16:"int(10) unsigned";}s:7:"network";a:5:{s:4:"type";s:13:"table:network";s:5:"label";s:18:"连接节点名称";s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:21:"mediumint(8) unsigned";}s:6:"method";a:5:{s:4:"type";s:12:"varchar(100)";s:5:"label";s:21:"同步的接口名称";s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:12:"varchar(100)";}s:6:"params";a:3:{s:4:"type";s:9:"serialize";s:7:"comment";s:35:"请求和响应的参数(序列化)";s:8:"realtype";s:8:"longtext";}s:8:"callback";a:5:{s:4:"type";s:12:"varchar(200)";s:5:"label";s:12:"回调地址";s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:12:"varchar(200)";}s:15:"callback_params";a:2:{s:4:"type";s:4:"text";s:8:"realtype";s:4:"text";}s:6:"result";a:5:{s:4:"type";s:4:"text";s:5:"label";s:21:"请求响应的结果";s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:4:"text";}s:10:"fail_times";a:8:{s:4:"type";s:7:"int(10)";s:7:"default";i:1;s:8:"required";b:1;s:5:"label";s:15:"失败的次数";s:10:"filtertype";s:6:"number";s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:7:"int(10)";}s:6:"status";a:7:{s:4:"type";a:2:{s:4:"succ";s:6:"成功";s:6:"failed";s:6:"失败";}s:7:"default";s:6:"failed";s:8:"required";b:1;s:5:"label";s:12:"交互状态";s:8:"editable";b:0;s:7:"in_list";b:1;s:8:"realtype";s:21:"enum('succ','failed')";}}s:5:"index";a:2:{s:15:"ind_rpc_task_id";a:2:{s:7:"columns";a:3:{i:0;s:2:"id";i:1;s:4:"type";i:2;s:10:"process_id";}s:6:"prefix";s:6:"unique";}s:19:"ind_rpc_response_id";a:2:{s:7:"columns";a:1:{i:0;s:10:"process_id";}s:4:"type";s:4:"hash";}}s:6:"engine";s:6:"innodb";s:7:"version";s:13:"$Rev: 40912 $";s:12:"ignore_cache";b:1;s:7:"comment";s:18:"ec-rpc连接池表";s:7:"in_list";a:10:{i:0;s:2:"id";i:1;s:10:"process_id";i:2;s:4:"type";i:3;s:8:"calltime";i:4;s:7:"network";i:5;s:6:"method";i:6;s:8:"callback";i:7;s:6:"result";i:8;s:10:"fail_times";i:9;s:6:"status";}s:15:"default_in_list";a:9:{i:0;s:2:"id";i:1;s:10:"process_id";i:2;s:4:"type";i:3;s:8:"calltime";i:4;s:7:"network";i:5;s:6:"method";i:6;s:8:"callback";i:7;s:6:"result";i:8;s:10:"fail_times";}s:8:"idColumn";s:10:"process_id";s:10:"textColumn";s:10:"process_id";}s:3:"ttl";i:0;s:8:"dateline";i:1490087623;}