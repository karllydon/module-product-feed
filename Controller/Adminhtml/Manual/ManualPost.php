<?php


namespace VaxLtd\ProductFeed\Controller\Adminhtml\Manual;

use Magento\Framework\Exception\LocalizedException;
use VaxLtd\ProductFeed\Logger\Logger;
use Vaxltd\ProductFeed\Model\ProfileFactory;
use VaxLtd\ProductFeed\Helper\Export;

class ManualPost extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var Export
     */
    protected $export;

    /**
     * ManualPost constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Logger $logger
     * @param ProfileFactory $profileFactory
     * @param Export $export

     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Logger $logger,
        ProfileFactory $profileFactory,
        Export $export
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->profileFactory = $profileFactory;
        $this->export = $export;

    }

    /**
     * Export action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        try {

            $data = $this->getRequest()->getParams();

        

            $entityIdFrom = $data['entity_id_from'] ?: null;
            $entityIdTo = $data['entity_id_to'] ?: null;

            $profile = $this->profileFactory->create()->load($data['profile_select']);

            if (!$profile->getId()) {
                $this->messageManager->addErrorMessage("Could not find required profile");
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('prodfeed/manual/index');
                return $resultRedirect;
            }

            $file = $this->export->export($profile, 'manual', $entityIdFrom, $entityIdTo);

            if (isset($data['download']) && $data['download'] && $file) {
                $fileData = stream_get_contents($file);
                fclose($file);
                if (!$fileData) {
                    throw new \Exception("Getting file data to download failed.");
                }
                /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
                $resultPage->setHttpResponseCode(200)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Content-type', 'application/octet-stream', true)
                    ->setHeader('Content-Length', strlen($fileData))
                    ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Content-Disposition', 'attachment; filename="' . "{$profile->getFilename()}.{$profile->getOutputType()}" . '"')
                    ->setHeader('Last-Modified', date('r'));
                $resultPage->setContents($fileData);

                $this->messageManager->addSuccessMessage("Profile {$profile->getId()} exported successfully");
                return $resultPage;
            } else {
                $this->messageManager->addSuccessMessage("Profile {$profile->getId()} exported successfully");
                $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('prodfeed/manual/index');
                return $resultRedirect;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage("Product Export failed.");
            $this->logger->error("Manual Product Export Failed: " . $e->getMessage());
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('prodfeed/manual/index');
            return $resultRedirect;
        }
    }
}