<?php

namespace VaxLtd\ProductFeed\Model;

class Log extends \Magento\Framework\Model\AbstractModel
{

    const RESULT_FAILURE = 0;
    const RESULT_SUCCESSFUL = 1;


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VaxLtd\ProductFeed\Model\ResourceModel\Log');
    }


}
