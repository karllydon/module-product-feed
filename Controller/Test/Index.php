<?php

namespace VaxLtd\ProductFeed\Controller\Test;

use Magento\Framework\Controller\Result\JsonFactory;
use VaxLtd\ProductFeed\Helper\Data;

class Index implements \Magento\Framework\App\ActionInterface

{

    protected $jsonFactory;
    protected $helper;

        
    public function __construct(
        JsonFactory $jsonFactory,
        Data $helper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->helper = $helper;
    }



    public function execute() {
        $resultJson = $this->jsonFactory->create();

        $catName = $this->helper->getAttSetGoogleCat(10);


      
        return $resultJson->setData(["Google Category Name" => $catName]);
    }


}