<?php

namespace VaxLtd\ProductFeed\Model\ResourceModel\Log;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VaxLtd\ProductFeed\Model\Log', 'VaxLtd\ProductFeed\Model\ResourceModel\Log');
    }
}