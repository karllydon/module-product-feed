<?php

namespace VaxLtd\ProductFeed\Helper;


use \VaxLtd\ProductFeed\Model\ExportMethod\ExportMethodFactory;
use VaxLtd\ProductFeed\Model\DestinationFactory;
use VaxLtd\ProductFeed\Logger\Logger;


class Export
{

    /**
     * @var ExportMethodFactory
     */
    protected $exportFactory;

    /**
     * @var DestinationFactory
     */
    protected $destinationFactory;

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @param ExportMethodFactory $exportFactory
     * @param DestinationFactory $destinationFactory
     * @param Logger $logger
     */
    public function __construct(ExportMethodFactory $exportFactory, DestinationFactory $destinationFactory, Logger $logger)
    {
        $this->exportFactory = $exportFactory;
        $this->destinationFactory = $destinationFactory;
        $this->logger = $logger;
    }


    /**
     * @param \VaxLTd\ProductFeed\Model\Profile $profile
     * @return mixed
     */
    public function export($profile)
    {


        $destinationIds = $profile->getDestinationIds();
        if (!$destinationIds) {
            $this->logger->error("Attempted to export profile {$profile->getId()}, no destinations found");
            return false;
        }

        $destinationIds = explode(",", $destinationIds);

        $result = true;

        foreach ($destinationIds as $destinationId) {
            $destination = $this->destinationFactory->create()->load($destinationId);
            $exportMethod = $this->exportFactory->create("VaxLtd\ProductFeed\Model\ExportMethod" . "\\" . ucfirst($destination->getType()));
            $result = $result && $exportMethod->uploadFile($profile, $destination);


            
        }
        return $result;
    }








}