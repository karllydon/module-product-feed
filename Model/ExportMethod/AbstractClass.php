<?php

namespace VaxLtd\ProductFeed\Model\ExportMethod;

use Magento\Framework\ObjectManagerInterface;
use VaxLtd\ProductFeed\Logger\Logger;
use VaxLtd\ProductFeed\Model\ExportMethod\ExportInterface;
use VaxLtd\ProductFeed\Model\ProductFeed;
use VaxLtd\Productfeed\Helper\Config;
use Magento\Framework\Event\ManagerInterface;

abstract class AbstractClass implements ExportMethodInterface
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $rows;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var string
     */
    protected $errorMsg = null;

    /**
     * @var bool
     */
    protected $exportSuccess = false;

    // /**
    //  * @var \Xtento\ProductExport\Model\DestinationFactory
    //  */
    // protected $destinationFactory;

    /**
     * AbstractClass constructor.
     * @param Logger $logger
     * @param Config $config
     * @param ProductFeed $prodfeed
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Logger $logger,
        Config $config,
        ProductFeed $prodfeed,
        ObjectManagerInterface $objectManager,
        ManagerInterface $eventManager
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->rows = $prodfeed->generateProductFeed();
        $this->config = $config;
        $this->eventManager = $eventManager;
    }

    protected function dispatchExportEvent($data = null){
        $this->eventManager->dispatch('vaxltd_productfeed_export', ['eventData' => $data]);
    }

}