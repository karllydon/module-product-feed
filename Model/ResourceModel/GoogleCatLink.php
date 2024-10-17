<?php

namespace VaxLtd\ProductFeed\Model\ResourceModel;

class GoogleCatLink extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('prodfeed_google_cat_link', 'entity_id');
    }
}