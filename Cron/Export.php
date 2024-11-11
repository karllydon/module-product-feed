<?php

namespace VaxLtd\ProductFeed\Cron;

use Magento\Framework\Exception\LocalizedException;

use VaxLtd\ProductFeed\Helper\Config;
use VaxLtd\ProductFeed\Logger\Logger;
use VaxLtd\ProductFeed\Model\ProfileFactory;
use VaxLtd\ProductFeed\Helper\Cron;
use VaxLtd\ProductFeed\Helper\Export as ExportHelper;

class Export
{


    /**
     * @var Config 
     */
    protected $config;


    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var Cron
     */
    protected $cronHelper;

    /**
     * @var ExportHelper
     */
    protected $export;


    public function __construct(Config $config, Logger $logger, ProfileFactory $profileFactory, Cron $cronHelper, ExportHelper $export)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->profileFactory = $profileFactory;
        $this->cronHelper = $cronHelper;
        $this->export = $export;
    }


    /**
     * Run automatic export, dispatched by Magento cron scheduler
     *
     * @param $schedule
     */
    public function execute($schedule)
    {

        try {
            if (!$this->config->isEnabled()) {
                $this->logger->info('Cronjob executed, but module is disabled');
                return true;
            }
            if (!$schedule) {
                $this->logger->info('Cronjob executed, but no schedule is defined for cron');
                return true;
            }
            $jobCode = $schedule->getJobCode();
            preg_match('/profile_(\d+)/', $jobCode, $jobMatch);
            if (!isset($jobMatch[1])) {
                throw new LocalizedException(__('No profile ID found in job_code.'));
            }
            $profileId = $jobMatch[1];
            $profile = $this->profileFactory->create()->load($profileId);
            if (!$profile->getId()) {
                // Remove existing cronjobs
                $this->cronHelper->removeCronjobsLike('productexport_profile_' . $profileId . '\_%', \VaxLtd\ProductFeed\Model\Profile::CRONGROUP);
                throw new LocalizedException(__("Profile ID {$profileId} does not seem to exist anymore."));
            }
            if (!$profile->getEnabled()) {
                return true; // Profile not enabled
            }
            if (!$profile->getCronjobEnabled()) {
                return true; // Cronjob not enabled
            }



            $this->export->export($profile, 'cron');





        } catch (\Exception $e) {
            $this->logger->critical('Cronjob exception for job_code ' . $jobCode . ': ' . $e->getMessage());
        }
        return true;
    }
}
