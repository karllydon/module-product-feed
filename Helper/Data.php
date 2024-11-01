<?php

namespace VaxLtd\ProductFeed\Helper;

use \VaxLtd\ProductFeed\Logger\Logger;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \VaxLtd\ProductFeed\Model\ResourceModel\Log;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $googleCatFactory;


    protected $catLinkCollectionFactory;


    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var Log
     */
    protected $logResource;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param  \VaxLtd\ProductFeed\Model\GoogleCatFactory $googleCatFactory
     * @param  \VaxLtd\ProductFeed\Model\ResourceModel\GoogleCatLink\CollectionFactory $catLinkCollectionFactory
     * @param Logger $logger
     * @param TimezoneInterface $localeDate
     * @param Log $logResource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \VaxLtd\ProductFeed\Model\GoogleCatFactory $googleCatFactory,
        \VaxLtd\ProductFeed\Model\ResourceModel\GoogleCatLink\CollectionFactory $catLinkCollectionFactory,
        Logger $logger,
        TimezoneInterface $localeDate,
        Log $logResource
    ) {
        $this->googleCatFactory = $googleCatFactory;
        $this->catLinkCollectionFactory = $catLinkCollectionFactory;
        $this->logger = $logger;
        $this->localeDate = $localeDate;
        $this->logResource = $logResource;
        parent::__construct($context);
    }

    /**
     * @param int $categoryId
     * @return string
     */
    public function getGoogleCat($categoryId)
    {
        $googleCatModel = $this->googleCatFactory->create()->load($categoryId);
        return $googleCatModel->getGoogleCategory();
    }

    /**
     * @param int $attribute_set_id
     * @return string
     */
    public function getAttSetGoogleCat($attribute_set_id)
    {
        $collection = $this->catLinkCollectionFactory->create();
        $googleCatLink = $collection->addFieldToFilter("attribute_set_id", ["eq" => $attribute_set_id])->getFirstItem();
        return $this->getGoogleCat($googleCatLink->getGoogleCategoryId());
    }

    /*
     * Convert date to local timezone timestamp. This is important so strftime() in the XSL Template returns the correct time zone.
     */
    public function convertDateToStoreTimestamp($date, $store = null)
    {
        try {
            // Temporary DateTime object to get scope timezone
            $tempLocaleDate = $this->localeDate->scopeDate(
                $store,
                $date,
                true
            );

            // Retrieve the correct store timezone - we can use the date above for source.
            $mageTimezone = $tempLocaleDate->getTimezone();

            // Create a temporary DateTime object with the utc date and utc timestamp
            $tempConversionDate = new \DateTime(is_numeric($date) ? '@' . $date : $date, new \DateTimeZone('UTC'));

            // Set the timezone correction to the newly created DateTime to make date conversion
            $tempConversionDate->setTimezone($mageTimezone);

            // Pass through a temporary string representation
            $convertedString = $tempConversionDate->format('Y-m-d H:i:s');

            // Convert string to timestamp since ->format('u') will always return in UTC regardless of set timestamp
            $convertedTimestamp = strtotime($convertedString);

            // Timezone-corrected timestamp
            return $convertedTimestamp;
        } catch (\Exception $e) {
            return null;
        }
    }

    /** 
     * @return string | null
     */
    public function getLastLogId()
    {
        return $this->logResource->getLastId();
    }




}