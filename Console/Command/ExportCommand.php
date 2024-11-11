<?php

namespace VaxLtd\ProductFeed\Console\Command;

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\AreaList as AreaList;
use Magento\Framework\App\Area as Area;
use Magento\SalesSequence\Model\ProfileFactory as ModelProfileFactory;
use Symfony\Component\Console\Command\Command;
use VaxLtd\ProductFeed\Helper\Export;
use VaxLtd\ProductFeed\Helper\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VaxLtd\ProductFeed\Logger\Logger;
use VaxLtd\ProductFeed\Model\ResourceModel\Profile\CollectionFactory as ProfileCollectionFactory;
use VaxLtd\ProductFeed\Model\ProfileFactory;
use VaxLtd\ProductFeed\Model\ProductFeed;

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
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var ProductFeed
     */
    protected $productFeed;


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
     * @param ProfileFactory $profileFactory
     * @param ProductFeed $productFeed
     */
    public function __construct(
        AppState $appState,
        AreaList $areaList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Export $helper,
        Config $config,
        Logger $logger,
        ProfileCollectionFactory $profileCollectionFactory,
        ProfileFactory $profileFactory,
        ProductFeed $productFeed
    ) {
        $this->appState = $appState;
        $this->areaList = $areaList;
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->config = $config;
        $this->logger = $logger;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->profileFactory = $profileFactory;
        $this->productFeed = $productFeed;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('vax:productfeed:export')
            ->setDescription('Run Product Feed product export')
            ->setDefinition([
                new InputArgument(
                    'profile',
                    InputArgument::REQUIRED,
                    'Profile IDs to export (multiple IDs: comma-separated). Or specify "list" to list all enabled profiles.'
                ),
                new InputArgument(
                    'entity_id_from',
                    InputArgument::OPTIONAL,
                    'Product Entity ids to export from'
                ),
                new InputArgument(
                    'entity_id_to',
                    InputArgument::OPTIONAL,
                    'Product Entity ids to export to'
                )
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try {
            $this->appState->setAreaCode(Area::AREA_CRONTAB);
            $configLoader = $this->objectManager->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class);
            $this->objectManager->configure($configLoader->load(Area::AREA_CRONTAB));
            $this->areaList->getArea(Area::AREA_CRONTAB)->load(Area::PART_TRANSLATE);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // intentionally left empty
        }

        echo sprintf("[Debug] App Area: %s\n", $this->appState->getAreaCode()); // Required to avoid "area code not set" error 
        $this->logger->info("Running CLI product export");


        try {
            if ($input->getArgument('profile') === 'list') {
                $output->writeln("<info>List of enabled profiles:</info>");
                $profileCollection = $this->profileCollectionFactory->create();
                if (!$profileCollection->count()) {
                    $output->writeln("<info>No Profiles to list</info>");
                    return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
                }
                foreach ($profileCollection as $profile) {
                    if (!$profile->getEnabled())
                        continue;
                    $output->writeln("<info>- {$profile->getName()} (ID: {$profile->getId()})</info>");
                }
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            }

            $profileIds = explode(",", $input->getArgument('profile'));
            if (empty($profileIds)) {
                $output->writeln("<error>Profile IDs to export missing.</error>");
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }
            $entity_id_from = null;
            $entity_id_to = null;

            if ($input->getArgument('entity_id_from')) {
                $entity_id_from = $input->getArgument('entity_id_from');
            }
            if ($input->getArgument('entity_id_to')) {
                $entity_id_to = $input->getArgument('entity_id_to');
            }

            if ($entity_id_from > $entity_id_to) {
                $output->writeln("<error>Entity ID from argument must be less that Entity ID to argument</error>");
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }


            $result = true;
            foreach ($profileIds as $profileId) {
                $profileModel = $this->profileFactory->create()->load($profileId);
                $output->writeln("<info>Loaded profile {$profileModel->getName()}</info>");
                $result = $result && $this->helper->export( $profileModel,'console', $entity_id_from, $entity_id_to);
            }
            if ($result) {
                $output->writeln("<info>Export Completed Successfully</info>");
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            } else {
                $output->writeln("<error>One or more profiles failed to export</error>");
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }



        } catch (\Exception $e) {
            $output->writeln("<error>Could not export products: {$e}</error>");
            $this->logger->error("Could not export products: {$e}");
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
