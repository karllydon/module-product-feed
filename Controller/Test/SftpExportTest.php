<?php

namespace VaxLtd\ProductFeed\Controller\Test;

use Magento\Framework\Controller\Result\JsonFactory;
use VaxLtd\ProductFeed\Helper\SftpExport;

class SftpExportTest implements \Magento\Framework\App\ActionInterface

{

    protected $jsonFactory;
    protected $helper;

        
    public function __construct(
        JsonFactory $jsonFactory,
        SftpExport $helper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->helper= $helper;
    }

    public function execute() {
        $resultJson = $this->jsonFactory->create();
        $exportSuccess = $this->helper->export();
        
        if (!$exportSuccess) {
            return $resultJson->setData(["message" => "Could not export to SFTP"]);    
        }

        return $resultJson->setData(["message" => "Sftp exported successfully"]);
    }


}