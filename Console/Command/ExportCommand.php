<?php

namespace VaxLtd\ProductFeed\Console\Command;

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\AreaList as AreaList;
use Magento\Framework\App\Area as Area;
use Symfony\Component\Console\Command\Command;
use VaxLtd\ProductFeed\Helper\SftpExport;
use VaxLtd\ProductFeed\Helper\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var SftpExport
     */
    protected $helper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * ExportCommand constructor.
     *
     * @param AppState $appState
     * @param AreaList $areaList
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param SftpExport $helper
     * @param Config $config
     */
    public function __construct(
        AppState $appState,
        AreaList $areaList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SftpExport $helper,
        Config $config
    ) {
        $this->appState = $appState;
        $this->areaList = $areaList;
        $this->objectManager = $objectManager;
        $this->helper = $helper;
        $this->config = $config;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('vax:productfeed:export')
            ->setDescription('Run Product Feed product export');
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
        try {
            $output->writeln("Beginning product export");
            if (!$this->config->isEnabled()) {
                $output->writeln("<error>Product export disabled</error>");
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }
            $exportSuccess = $this->helper->export();
            if (!$exportSuccess){
                throw new \Exception("SFTP Export Failed");
            }

            $output->writeln("<info>Product export successful.</info>");
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Could not export products: {$e}</error>");
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
