<?php

namespace VaxLtd\ProductFeed\Model;

class GoogleCat extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VaxLtd\ProductFeed\Model\ResourceModel\GoogleCat');
    }
}
