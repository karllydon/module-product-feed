<?php

namespace VaxLtd\ProductFeed\Model\ExportMethod;

use \Magento\Framework\ObjectManagerInterface;


class ExportMethodFactory {

  /**
   * @var ObjectManagerInterface
   */
  protected $objectManager;


  public function __construct(
    ObjectManagerInterface $objectManager
  ){
    $this->objectManager = $objectManager;
  }
  public function create($className, array $data = [])
  {
      $instance = $this->objectManager->create($className, $data);
      if (!($instance instanceof \VaxLtd\ProductFeed\Model\ExportMethod\ExportMethodInterface)) {
            throw new \InvalidArgumentException(
                'Class must implement \VaxLtd\ProductFeed\Model\ExportMethod\ExportMethodInterface'
            );
      }
      return $instance;
   }
}