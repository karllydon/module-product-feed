<?php

namespace VaxLtd\ProductFeed\Helper;


use \VaxLtd\ProductFeed\Model\ExportMethod\ExportMethodFactory;
use VaxLtd\ProductFeed\Model\DestinationFactory;
use VaxLtd\ProductFeed\Logger\Logger;
use VaxLtd\ProductFeed\Model\ProductFeed;
use VaxLtd\ProductFeed\Model\Format\FormatFactory;


class Export
{


    private $EXPORTTYPES = [
        "console" =>0
    ];

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
     * @var ProductFeed
     */
    protected $prodFeed;


    /**
     * @var FormatFactory
     */
    protected $formatFactory;


    /**
     * @param ExportMethodFactory $exportFactory
     * @param DestinationFactory $destinationFactory
     * @param Logger $logger
     * @param ProductFeed $prodFeed
     * @param FormatFactory $formatFactory
     */
    public function __construct(ExportMethodFactory $exportFactory, DestinationFactory $destinationFactory, Logger $logger, ProductFeed $prodFeed, FormatFactory $formatFactory)
    {
        $this->exportFactory = $exportFactory;
        $this->destinationFactory = $destinationFactory;
        $this->logger = $logger;
        $this->prodFeed = $prodFeed;
        $this->formatFactory = $formatFactory;
    }


    /**
     * @param \VaxLtd\ProductFeed\Model\Profile $profile
     * @param string $exportType
     * @param int|null $entity_id_from
     * @param int|null $entity_id_to
     * @return mixed
     */
    public function export($profile, $exportType, $entity_id_from = null, $entity_id_to = null)
    {

        if (!in_array($exportType, array_keys($this->EXPORTTYPES))) {
            $this->logger->error("Attempted to export profile {$profile->getId()} but incorrect export type {$exportType}, export type should be a memeber of " . print_r($this->EXPORTTYPES, true));
            return false;
        }



        $profile->saveLastExecutionNow();
        $destinationIds = $profile->getDestinationIds();
        if (!$destinationIds) {
            $this->logger->error("Attempted to export profile {$profile->getId()}, no destinations found");
            return false;
        }

        $destinationIds = explode(",", $destinationIds);

        $result = true;

        $data = $this->prodFeed->generateProductFeed($profile, $entity_id_from, $entity_id_to);
        $count = count($data);
        $format = $this->formatFactory->create("\VaxLtd\ProductFeed\Model\Format" . "\\" . ucfirst($profile->getOutputType()));

        $file = $format->formatFile($data);

        if (!$file) {
            throw new \Exception("Could not format prodfeed to {$profile->getOutputType()}");
        }



        foreach ($destinationIds as $destinationId) {
            $destination = $this->destinationFactory->create()->load($destinationId);
            $exportMethod = $this->exportFactory->create("VaxLtd\ProductFeed\Model\ExportMethod" . "\\" . ucfirst($destination->getType()));
            $exportResult = $exportMethod->uploadFile($profile, $destination, $count, $file);
            $exportMethod->dispatchExportEvent($destination->getId(), $this->EXPORTTYPES[$exportType], $count);
            $destination->setLastResult($exportResult);
            if ($exportMethod->getErrorMsg()) {
                $destination->setLastResultMessage($exportMethod->getErrorMsg());
            } else {
                $destination->setLastResultMessage($exportMethod->getSuccessMsg());
            }





            $result = $result && $exportResult;
            rewind($file);
        }
        fclose($file);
        return $result;
    }








}