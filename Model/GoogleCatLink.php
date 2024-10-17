<?php

namespace VaxLtd\ProductFeed\Model;

class GoogleCatLink extends \Magento\Framework\Model\AbstractModel
{   
    /**
     * @var GoogleCat
     */
    protected $googleCat;


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(resourceModel: 'VaxLtd\ProductFeed\Model\ResourceModel\GoogleCatLink');
    }


}
