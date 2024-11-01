<?php

namespace VaxLtd\ProductFeed\Model\ResourceModel;

class Destination extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('prodfeed_destination', 'destination_id');
    }
}