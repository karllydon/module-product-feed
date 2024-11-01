<?php

namespace VaxLtd\ProductFeed\Model\ResourceModel;

class Log extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('prodfeed_export_log', 'log_id');
    }


    public function getLastId(){
        return $this->getConnection()->lastInsertId($this->getMainTable());
    }


}