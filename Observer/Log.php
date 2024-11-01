<?php
/**
 * Copyright &copy; Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace VaxLtd\ProductFeed\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use VaxLtd\ProductFeed\Logger\Logger;
use VaxLtd\ProductFeed\Model\LogFactory;


class Log implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @param Logger $logger
     * @param LogFactory $logFactory
     */
    public function __construct(Logger $logger, LogFactory $logFactory)
    {
        $this->logger = $logger;
        $this->logFactory = $logFactory;
    }

    public function execute(Observer $observer)
    {
        $data = $observer->getData('eventData');
        $log = $this->logFactory->create();
        $log->setExportType($data['type']);

        if ($data['errorMsg']) {
            $log->setRecordsExported(0)->setResult(0)->setResultMessage($data['errorMsg'])->save();
        } else {
            $log->setRecordsExported($data['records'])->setResult(1)->setResultMessage("Exported {$data['records']} products in {$data['duration']} seconds")->save();
        }
        $this->logger->info("Logged Product Export {$log->getLogId()}");
    }
}