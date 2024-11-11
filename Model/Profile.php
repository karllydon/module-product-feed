<?php

namespace VaxLtd\ProductFeed\Model;

use VaxLtd\ProductFeed\Helper\Cron;
use Magento\Framework\App\RequestInterface;
use VaxLtd\ProductFeed\Logger\Logger;



class Profile extends \Magento\Framework\Model\AbstractModel
{


    const CRONGROUP = 'vaxltd_productfeed';





    /**
     * @var Cron
     */
    protected $cronHelper;


    /**
     * @var RequestInterface
     */
    protected $request;


    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('VaxLtd\ProductFeed\Model\ResourceModel\Profile');
    }


    /**
     * Summary of __construct
     * @param \VaxLtd\ProductFeed\Helper\Cron $cronhelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param Logger $logger
     * @param array $data
     */
    public function __construct(
        Cron $cronhelper,
        RequestInterface $request,
        Logger $logger,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->cronHelper = $cronhelper;
        $this->request = $request;
        $this->logger = $logger;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    public function saveLastExecutionNow()
    {
        $write = $this->getResource()->getConnection();
        $write->update(
            $this->getResource()->getMainTable(),
            ['last_execution' => (new \DateTime)->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)],
            ["`{$this->getResource()->getIdFieldName()}` = {$this->getId()}"]
        );
    }

    public function saveLastModificationNow()
    {
        $write = $this->getResource()->getConnection();
        $write->update(
            $this->getResource()->getMainTable(),
            ['last_modification' => (new \DateTime)->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)],
            ["`{$this->getResource()->getIdFieldName()}` = {$this->getId()}"]
        );
    }



    /**
     * Update database via cron helper
     */
    protected function updateCronjobs()
    {
        // Remove existing cronjobs
        $this->cronHelper->removeCronjobsLike('vax_productfeed_profile_' . $this->getId() . '\_%', self::CRONGROUP);

        if (!$this->getEnabled()) {
            return $this; // Profile not enabled
        }
        if (!$this->getCronjobEnabled()) {
            return $this; // Cronjob not enabled
        }

        
        $cronRunModel = 'VaxLtd\ProductFeed\Cron\Export::execute';
        if ($this->getCronjobFrequencies() && $this->getCronjobFrequencies() !== '') {
            // Custom cron expression
            $cronFrequencies = $this->getCronjobFrequencies();
            if (empty($cronFrequencies)) {
                return $this;
            }
            $cronFrequencies = array_unique(explode(";", $cronFrequencies));
            $cronCounter = 0;
            foreach ($cronFrequencies as $cronFrequency) {
                $cronFrequency = trim($cronFrequency);
                if (empty($cronFrequency)) {
                    continue;
                }
                $cronCounter++;
                $cronIdentifier = 'productfeed_profile_' . $this->getId() . '_cron_' . $cronCounter;
                $this->cronHelper->addCronjob(
                    $cronIdentifier,
                    $cronFrequency,
                    $cronRunModel,
                    self::CRONGROUP
                );
            }
        } 
        return $this;
    }


    public function afterSave()
    {
        parent::afterSave();

        if ($this->request->getModuleName() == 'prodfeed' && ($this->request->getControllerName() == 'profile')) {
            $this->updateCronjobs();
        }
        return $this;
    }




    public function beforeDelete()
    {
        // Remove existing cronjobs
        $this->cronHelper->removeCronjobsLike('vax_productfeed_profile_' . $this->getId() . '\_%', self::CRONGROUP);

        return parent::beforeDelete();
    }







}

