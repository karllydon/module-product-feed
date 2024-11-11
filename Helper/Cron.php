<?php

namespace VaxLtd\ProductFeed\Helper;

use \Magento\Framework\App\ResourceConnection;
use \Magento\Framework\App\Config\ValueFactory;


use VaxLtd\ProductFeed\Logger\Logger;


class Cron
{

    const CRON_PATH_PREFIX = 'crontab/%s/jobs/vax_';


    /**
     * @var ResourceConnection
     **/
    protected $resourceConnection;

    /**
     * @var ValueFactory
     */
    protected $configValueFactory;

    protected $logger;



    public function __construct(ResourceConnection $resourceConnnection, ValueFactory $configValueFactory, Logger $logger)
    {
        $this->resourceConnection = $resourceConnnection;
        $this->configValueFactory = $configValueFactory;
        $this->logger = $logger;
    }


    /**
     * Remove cronjob from database
     *
     * @param $cronIdentifier
     * @param $cronGroup
     * @return $this
     */
    public function removeCronjob($cronIdentifier, $cronGroup = 'default')
    {
        $this->configValueFactory->create()
            ->load($this->getCronExpressionConfigPath($cronIdentifier, $cronGroup), 'path')->delete();
        $this->configValueFactory->create()
            ->load($this->getCronRunModelConfigPath($cronIdentifier, $cronGroup), 'path')->delete();

        if ($cronGroup != 'default') {
            // Remove legacy cronjobs
            $this->configValueFactory->create()
                ->load($this->getCronExpressionConfigPath($cronIdentifier, 'default'), 'path')->delete();
            $this->configValueFactory->create()
                ->load($this->getCronRunModelConfigPath($cronIdentifier, 'default'), 'path')->delete();
        }

        return $this;
    }


    public function removeCronjobsLike($cronIdentifier, $cronGroup = 'default')
    {
        if (empty($cronIdentifier)) {
            return $this;
        }

        $configTable = $this->resourceConnection->getTableName('core_config_data');
        $connection = $this->resourceConnection->getConnection();
        $connection->delete($configTable, ['path LIKE ?' => sprintf(self::CRON_PATH_PREFIX, $cronGroup) . $cronIdentifier]);

        if ($cronGroup != 'default') {
            // Remove legacy cronjobs
            $connection->delete($configTable, ['path LIKE ?' => sprintf(self::CRON_PATH_PREFIX, 'default') . $cronIdentifier]);
        }

        return $this;
    }

    /**
     * Add cronjob to database
     *
     * @param $cronIdentifier
     * @param $cronExpression
     * @param $cronRunModel
     * @param $cronGroup
     * @return $this
     */
    public function addCronjob($cronIdentifier, $cronExpression, $cronRunModel, $cronGroup = 'default')
    {


        $this->configValueFactory->create()->load(
            $this->getCronExpressionConfigPath($cronIdentifier, $cronGroup),
            'path'
        )->setValue(
                $cronExpression
            )->setPath(
                $this->getCronExpressionConfigPath($cronIdentifier, $cronGroup)
            )->save();

        $this->configValueFactory->create()->load(
            $this->getCronRunModelConfigPath($cronIdentifier, $cronGroup),
            'path'
        )->setValue(
                $cronRunModel
            )->setPath(
                $this->getCronRunModelConfigPath($cronIdentifier, $cronGroup)
            )->save();

        return $this;
    }



    /**
     * Get config path to save cron expression in
     *
     * @param $cronIdentifier
     * @param $cronGroup
     * @return string
     */
    protected function getCronExpressionConfigPath($cronIdentifier, $cronGroup)
    {
        return sprintf(self::CRON_PATH_PREFIX, $cronGroup) . $cronIdentifier . '/schedule/cron_expr';
    }

    /**
     * Get config path to save cron run model in
     *
     * @param $cronIdentifier
     * @param $cronGroup
     *
     * @return string
     */
    protected function getCronRunModelConfigPath($cronIdentifier, $cronGroup)
    {
        return sprintf(self::CRON_PATH_PREFIX, $cronGroup) . $cronIdentifier . '/run/model';
    }

}