<?php exit(); ?>a:3:{s:5:"value";a:9:{s:7:"columns";a:9:{s:10:"content_id";a:5:{s:4:"type";s:6:"number";s:4:"pkey";b:1;s:5:"extra";s:14:"auto_increment";s:7:"comment";s:6:"序号";s:8:"realtype";s:21:"mediumint(8) unsigned";}s:12:"content_type";a:7:{s:4:"type";s:11:"varchar(80)";s:8:"required";b:1;s:5:"width";i:100;s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:7:"comment";s:41:"service类型(service_category和service)";s:8:"realtype";s:11:"varchar(80)";}s:6:"app_id";a:7:{s:4:"type";s:10:"table:apps";s:8:"required";b:1;s:5:"width";i:100;s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:7:"comment";s:15:"应用的app_id";s:8:"realtype";s:11:"varchar(32)";}s:12:"content_name";a:3:{s:4:"type";s:11:"varchar(80)";s:7:"comment";s:34:"service category name - service id";s:8:"realtype";s:11:"varchar(80)";}s:13:"content_title";a:4:{s:4:"type";s:12:"varchar(100)";s:8:"is_title";b:1;s:7:"comment";s:7:"optname";s:8:"realtype";s:12:"varchar(100)";}s:12:"content_path";a:3:{s:4:"type";s:12:"varchar(255)";s:7:"comment";s:36:"class name只有type为service才有";s:8:"realtype";s:12:"varchar(255)";}s:8:"ordernum";a:4:{s:4:"type";s:11:"smallint(4)";s:7:"default";i:50;s:5:"label";s:6:"排序";s:8:"realtype";s:11:"smallint(4)";}s:10:"input_time";a:3:{s:4:"type";s:4:"time";s:5:"label";s:12:"加载时间";s:8:"realtype";s:16:"int(10) unsigned";}s:8:"disabled";a:4:{s:4:"type";s:4:"bool";s:7:"default";s:5:"false";s:7:"comment";s:12:"是否有效";s:8:"realtype";s:20:"enum('true','false')";}}s:5:"index";a:1:{s:16:"ind_content_type";a:1:{s:7:"columns";a:1:{i:0;s:12:"content_type";}}}s:7:"version";s:13:"$Rev: 44008 $";s:7:"comment";s:45:"app资源信息表, 记录app的service信息";s:8:"idColumn";s:10:"content_id";s:5:"pkeys";a:1:{s:10:"content_id";s:10:"content_id";}s:7:"in_list";a:2:{i:0;s:12:"content_type";i:1;s:6:"app_id";}s:15:"default_in_list";a:2:{i:0;s:12:"content_type";i:1;s:6:"app_id";}s:10:"textColumn";s:13:"content_title";}s:3:"ttl";i:0;s:8:"dateline";i:1490087304;}