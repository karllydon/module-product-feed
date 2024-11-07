<?php

namespace VaxLtd\ProductFeed\Model\Format;

use VaxLtd\ProductFeed\Logger\Logger;
use Magento\Framework\Serialize\Serializer\Json;



abstract class AbstractFormat implements FormatInterface {

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @var Json
     */
    protected $json;

    /**
     * Summary of __construct
     * @param Logger $logger
     * @param Json $json
     */
    public function __construct(Logger $logger, Json $json){
        $this->logger = $logger;
        $this->json = $json;
    }



}


