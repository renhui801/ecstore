<?php
/**
 * ShopEx网上商店 邮件地址模型层
 * 处理邮件订阅的邮件地址
 *
 * @package
 * @version $Id$
 * @author
 * @copyright 2003-2008 Shanghai ShopEx Network Tech. Co., Ltd.
 * @license Commercial
 * =================================================================
 * 版权所有 (C) 2003-2009 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:http://www.shopex.cn/
 * -----------------------------------------------------------------
 * 您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * =================================================================
 */

 class  emailsubs_mdl_emailaddr extends dbeav_model{
     public $filter_use_like = true;

     function __construct(&$app){
        parent::__construct($app);
     }
 }