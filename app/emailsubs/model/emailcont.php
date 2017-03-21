<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class emailsubs_mdl_emailcont extends dbeav_model{
    public $filter_use_like = true;
    public $defaultOrder = ' ec_addtime desc';

    function __construct(&$app){
        parent::__construct($app);
    }

}
