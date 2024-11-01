<?php

namespace VaxLtd\ProductFeed\Console\Command;

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\AreaList as AreaList;
use Magento\Framework\App\Area as Area;
use Symfony\Component\Console\Command\Command;
use VaxLtd\ProductFeed\Helper\Export;
use VaxLtd\ProductFeed\Helper\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VaxLtd\ProductFeed\Logger\Logger;
use VaxLtd\ProductFeed\Model\ResourceModel\Profile\CollectionFactory as ProfileCollectionFactory;

class ExportCommand extends Command
{
    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var AreaList
     */
    protected $areaList;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Export
     */
    protected $helper;

    /**
     * @var Config
     */
     protected $config;


     /**
      * @var Logger
      */
    protected $logger;

    /**
     * @var ProfileCollectionFactory
     */
    protected $profileCollectionFactory;


    /**
     * ExportCommand constructor.
     *
     * @param AppState $appState
     * @param AreaList $areaList
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Export $helper
     * @param Config $config
     * @param Logger $logger
     * @param ProfileCollectionFactory $profileCollectionFactory
     */
    public function __construct(
        AppState $appState,
        AreaList $areaList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Export $helper,
        Config $config,
        Logger $logger,
        ProfileCollectionFactory $profileCollectionFactory
    ) {
        $this->appState = $appState;
        $this->areaList = $areaList;
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->config = $config;
        $this->logger = $logger;
        $this->profileCollectionFactory = $profileCollectionFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('vax:productfeed:export')
            ->setDescription('Run Product Feed product export')
            ->setDefinition(
                [
                    new InputArgument(
                        'profile',
                        InputArgument::REQUIRED,
                        'Profile IDs to export (multiple IDs: comma-separated). Or specify "list" to list all enabled profiles.'
                    ),
                ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->info("Running CLI product export");



        if ($input->getArgument('profile') === 'list') {
            $output->writeln(sprintf("<info>List of enabled profiles:</info>"));
            $profileCollection = $this->profileFactory->create()->getCollection();
            foreach ($profileCollection as $profile) {
                if (!$profile->getEnabled()) continue;
                $output->writeln(sprintf("<info>- %s (ID: %d)</info>", $profile->getName(), $profile->getId()));
            }
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        }






        // try {
        //     $this->appState->setAreaCode(Area::AREA_CRONTAB);
        //     $configLoader = $this->objectManager->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class);
        //     $this->objectManager->configure($configLoader->load(Area::AREA_CRONTAB));
        //     $this->areaList->getArea(Area::AREA_CRONTAB)->load(Area::PART_TRANSLATE);
        // } catch (\Magento\Framework\Exception\LocalizedException $e) {
        //     // intentionally left empty
        // }
        // echo sprintf("[Debug] App Area: %s\n", $this->appState->getAreaCode()); // Required to avoid "area code not set" error 
        // try {
        //     $output->writeln("Beginning product export");
        //     if (!$this->config->isEnabled()) {
        //         $output->writeln("<error>Product export disabled</error>");
        //         $this->logger->info("Product export disabled");
        //         return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        //     }
        //     $exportSuccess = $this->helper->export("local", "feedoptimise.csv");
        //     if (!$exportSuccess){
        //         throw new \Exception("SFTP Export Failed");
        //     }

        //     $output->writeln("<info>Product export successful.</info>");
        //     $this->logger->info("<info>Product export successful.</info>");
        //     return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        // } catch (\Exception $e) {
        //     $output->writeln("<error>Could not export products: {$e}</error>");
        //     $this->logger->error("Could not export products: {$e}");
        //     return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        // }
    }
}
