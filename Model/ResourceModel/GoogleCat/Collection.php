<?php

namespace VaxLtd\ProductFeed\Model\ResourceModel\GoogleCat;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VaxLtd\ProductFeed\Model\GoogleCat', 'VaxLtd\ProductFeed\Model\ResourceModel\GoogleCat');
    }
}