<?php

namespace VaxLtd\ProductFeed\Model\ResourceModel;

class Profile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('prodfeed_profile', 'profile_id');
    }
}