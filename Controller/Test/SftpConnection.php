<?php

namespace VaxLtd\ProductFeed\Controller\Test;

use Magento\Framework\Controller\Result\JsonFactory;
use VaxLtd\ProductFeed\Model\SftpWrapper;

class SftpConnection implements \Magento\Framework\App\ActionInterface

{

    protected $jsonFactory;
    protected $sftp;

        
    public function __construct(
        JsonFactory $jsonFactory,
        SftpWrapper $sftp
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->sftp = $sftp;
    }

    public function execute() {
        $resultJson = $this->jsonFactory->create();
        $message = $this->sftp->testConnection()? "SFTP connected": "Could not establish SFTP connection";  
        return $resultJson->setData(["message" => $message]);
    }


}