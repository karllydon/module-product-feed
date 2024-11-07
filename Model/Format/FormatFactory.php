<?php

namespace VaxLtd\ProductFeed\Model\Format;

use \Magento\Framework\ObjectManagerInterface;



class FormatFactory
{

  /**
   * @var ObjectManagerInterface
   */
  protected $objectManager;


  public function __construct(
    ObjectManagerInterface $objectManager
  ) {
    $this->objectManager = $objectManager;
  }
  public function create($className, array $data = [])
  {
    $instance = $this->objectManager->create($className, $data);
    if (!($instance instanceof \VaxLtd\ProductFeed\Model\Format\FormatInterface)) {
      throw new \InvalidArgumentException(
        'Class must implement \VaxLtd\ProductFeed\Model\Format\FormatInterface'
      );
    }
    return $instance;
  }
}