<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace VaxLtd\ProductFeed\Model\Config\Source;


use VaxLtd\ProductFeed\Model\ResourceModel\Destination\CollectionFactory;


class DestinationList implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * Summary of collection
     * @var \VaxLtd\ProductFeed\Model\ResourceModel\Destination\Collection
     */
    protected $collection;

    /**
     * Summary of __construct
     * @param \VaxLtd\ProductFeed\Model\ResourceModel\Destination\CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collection = $collectionFactory->create();
    }





    public function toOptionArray()
    {
        $optArray = [];
        foreach ($this->collection as $destination) {
            $optArray[] = ['value' => $destination->getId(), 'label' => $destination->getName()];
        }
        return $optArray;
    }
}
