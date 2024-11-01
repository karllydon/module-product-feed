<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VaxLtd\ProductFeed\Controller\Adminhtml\Destination;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use VaxLtd\ProductFeed\Model\DestinationFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    protected $scopeConfig;

    protected $_escaper;
    protected $inlineTranslation;
    protected $_dateFactory;

    /**
     * @var DestinationFactory
     */
    protected $destinationFactory;

    //protected $_modelNewsFactory;
    //  protected $collectionFactory;
    //  protected $filter;
    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        DestinationFactory $destinationFactory
    ) {
        // $this->filter = $filter;
        // $this->collectionFactory = $collectionFactory;
        $this->dataPersistor = $dataPersistor;
        $this->scopeConfig = $scopeConfig;
        $this->_escaper = $escaper;
        $this->_dateFactory = $dateFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->destinationFactory = $destinationFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('destination_id');
            $model = $this->destinationFactory->create()->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Destination no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $this->inlineTranslation->suspend();
            try {
                unset($data['form_key']);
                if (!$id){
                    unset($data['destination_id']);
                }
                $model->setData($data);
                $model->save();
                $this->messageManager->addSuccessMessage(__('Destination Saved successfully'));
                $this->dataPersistor->clear('prodfeed_destination');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['destination_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e, __('Something went wrong while saving the destination.'));
            }

            $this->dataPersistor->set('prodfeed_destination', $data);
            return $resultRedirect->setPath('*/*/edit', ['destination_id' => $this->getRequest()->getParam('destination_id')]);

        }


        return $resultRedirect->setPath('*/*/');

    }
}
