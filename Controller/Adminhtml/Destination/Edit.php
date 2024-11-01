<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VaxLtd\ProductFeed\Controller\Adminhtml\Destination;

use VaxLtd\ProductFeed\Logger\Logger;

use Magento\Backend\App\Action;

class Edit extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $logger;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        Logger $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VaxLtd_ProductFeed::destination');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('VaxLtd_ProductFeed::destination')
            ->addBreadcrumb(__('Destination'), __('Destination'))
            ->addBreadcrumb(__('Manage Destination'), __('Manage Destination'));
        return $resultPage;
    }

    /**
     * Edit CMS page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('destination_id');
        $model = $this->_objectManager->create('VaxLtd\ProductFeed\Model\Destination');
        $this->logger->debug("EDIT ID " . $id);
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This record no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);

        $this->logger->debug("EDIT DATA " . print_r($data, true));


        if (!empty($data)) {
            $model->setData($data);
        }
        
        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('destination', $model);
        
        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Destination') : __('New Destination'),
            $id ? __('Edit Destination') : __('New Destination')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Destination'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __("Edit Destination") : __('New Destination'));
        
        return $resultPage;
    }
}