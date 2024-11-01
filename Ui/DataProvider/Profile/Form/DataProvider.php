<?php

namespace VaxLtd\ProductFeed\Ui\DataProvider\Profile\Form;

use VaxLtd\ProductFeed\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \VaxLtd\ProductFeed\Model\ResourceModel\Profile\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;


    protected $_storeManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $profileCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        StoreManagerInterface $storeManager,
        CollectionFactory $profileCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $profileCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();

        /** @var $profile \VaxLtd\ProductFeed\Model\Profile */
        foreach ($items as $profile) {
            $profile = $profile->load($profile->getId()); //temporary fix

            $data = $profile->getData();

            $this->loadedData[$profile->getId()] = $data;
        }

        $data = $this->dataPersistor->get('profile');
        if (!empty($data)) {
            $profile = $this->collection->getNewEmptyItem();
            $profile->setData($data);
            $this->loadedData[$profile->getId()] = $profile->getData();
            $this->dataPersistor->clear('profile');
        }

        return $this->loadedData;
    }
}