<?php

namespace VaxLtd\ProductFeed\Model\ResourceModel\GoogleCatLink;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VaxLtd\ProductFeed\Model\GoogleCatLink', 'VaxLtd\ProductFeed\Model\ResourceModel\GoogleCatLink');
    }
}