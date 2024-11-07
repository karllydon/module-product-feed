<?php

namespace VaxLtd\ProductFeed\Model\ExportMethod;

use Magento\Framework\ObjectManagerInterface;
use VaxLtd\ProductFeed\Logger\Logger;
use VaxLtd\ProductFeed\Model\ExportMethod\ExportMethodInterface;
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
     * @var string
     */
    protected $successMsg = null;

    /**
     * @var bool
     */
    protected $exportSuccess = false;


    /**
     * @var int
     */
    protected $duration = null;

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
        ManagerInterface $eventManager,
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->eventManager = $eventManager;
    }
    /**
     * @param string $type
     * @param int $count
     * @return void
     */
    public function dispatchExportEvent($destination_id, $type, $count)
    {
        $this->eventManager->dispatch('vaxltd_productfeed_export', ['eventData' => ['destination_id' => $destination_id, 'type' => $type, 'records' => $count, 'errorMsg' => $this->errorMsg, 'successMsg' => $this->successMsg]]);
    }

    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    public function getSuccessMsg()
    {
        return $this->successMsg;
    }

}