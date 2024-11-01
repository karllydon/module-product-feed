<?php

namespace VaxLtd\ProductFeed\Model\ResourceModel\Destination;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VaxLtd\ProductFeed\Model\Destination', 'VaxLtd\ProductFeed\Model\ResourceModel\Destination');
    }
}