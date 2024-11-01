<?php

namespace VaxLtd\ProductFeed\Model\ResourceModel\Profile;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VaxLtd\ProductFeed\Model\Profile', 'VaxLtd\ProductFeed\Model\ResourceModel\Profile');
    }
}