<?php

namespace VaxLtd\ProductFeed\Model\ResourceModel;

class GoogleCat extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('prodfeed_google_cat', 'entity_id');
    }
}