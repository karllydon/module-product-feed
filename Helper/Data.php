<?php

namespace VaxLtd\ProductFeed\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $googleCatFactory;


    protected $catLinkCollectionFactory;

 
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \VaxLtd\ProductFeed\Model\GoogleCatFactory $googleCatFactory,
        \VaxLtd\ProductFeed\Model\ResourceModel\GoogleCatLink\CollectionFactory $catLinkCollectionFactory
    )
    {
        $this->googleCatFactory = $googleCatFactory;
        $this->catLinkCollectionFactory = $catLinkCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @param int $categoryId
     * @return string
     */
    public function getGoogleCat($categoryId){
        $googleCatModel = $this->googleCatFactory->create()->load($categoryId);
        return $googleCatModel->getGoogleCategory();
    }

    /**
     * @param int $attribute_set_id
     * @return string
     */
    public function getAttSetGoogleCat($attribute_set_id){
        $collection = $this->catLinkCollectionFactory->create();
        $googleCatLink = $collection->addFieldToFilter("attribute_set_id", ["eq" => $attribute_set_id])->getFirstItem();
        return $this->getGoogleCat($googleCatLink->getGoogleCategoryId());
    }



   
}