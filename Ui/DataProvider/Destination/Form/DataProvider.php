<?php

namespace VaxLtd\ProductFeed\Ui\DataProvider\Destination\Form;

use VaxLtd\ProductFeed\Model\ResourceModel\Destination\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \VaxLtd\ProductFeed\Model\ResourceModel\Destination\Collection
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
     * @param CollectionFactory $destinationCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        StoreManagerInterface $storeManager,
        CollectionFactory $destinationCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $destinationCollectionFactory->create();
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

        /** @var $destination \VaxLtd\ProductFeed\Model\Destination */
        foreach ($items as $destination) {
            $destination = $destination->load($destination->getId()); //temporary fix

            $data = $destination->getData();

            $this->loadedData[$destination->getId()] = $data;
        }

        $data = $this->dataPersistor->get('destination');
        if (!empty($data)) {
            $destination = $this->collection->getNewEmptyItem();
            $destination->setData($data);
            $this->loadedData[$destination->getId()] = $destination->getData();
            $this->dataPersistor->clear('destination');
        }

        return $this->loadedData;
    }
}