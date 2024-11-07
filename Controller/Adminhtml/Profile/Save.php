<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VaxLtd\ProductFeed\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use VaxLtd\ProductFeed\Model\ProfileFactory;

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
     * @var ProfileFactory
     */
    protected $profileFactory;

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
        ProfileFactory $profileFactory
    ) {
        // $this->filter = $filter;
        // $this->collectionFactory = $collectionFactory;
        $this->dataPersistor = $dataPersistor;
        $this->scopeConfig = $scopeConfig;
        $this->_escaper = $escaper;
        $this->_dateFactory = $dateFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->profileFactory = $profileFactory;
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
            $id = $this->getRequest()->getParam('profile_id');
            $model = $this->profileFactory->create()->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Profile no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            if ($model->getId()){
                $model->saveLastModificationNow();
            }

            $this->inlineTranslation->suspend();
            try {
                unset($data['form_key']);
                if (!$id) {
                    unset($data['profile_id']);
                }
                if ($data['destination_ids']) {
                    $data['destination_ids'] = implode(",", $data['destination_ids']);
                }

                if ($data['export_filter_product_visibility']) {
                    $data['export_filter_product_visibility'] = implode(",", $data['export_filter_product_visibility']);
                }

                if ($data['export_filter_product_type']) {
                    $data['export_filter_product_type'] = implode(',', $data['export_filter_product_type']);
                }


                $model->setData($data);
                $model->save();
                $this->messageManager->addSuccessMessage(__('Profile Saved successfully'));
                $this->dataPersistor->clear('prodfeed_profile');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['profile_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e, __('Something went wrong while saving the profile.'));
            }

            $this->dataPersistor->set('prodfeed_profile', $data);
            return $resultRedirect->setPath('*/*/edit', ['profile_id' => $this->getRequest()->getParam('profile_id')]);

        }


        return $resultRedirect->setPath('*/*/');

    }
}
