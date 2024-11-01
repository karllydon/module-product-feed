<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace VaxLtd\ProductFeed\Controller\Adminhtml\Profile;

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
        return $this->_authorization->isAllowed('VaxLtd_ProductFeed::profile');
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
        $resultPage->setActiveMenu('VaxLtd_ProductFeed::profile')
            ->addBreadcrumb(__('Profile'), __('Profile'))
            ->addBreadcrumb(__('Manage Profile'), __('Manage Profile'));
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
        $id = $this->getRequest()->getParam('profile_id');
        $model = $this->_objectManager->create('VaxLtd\ProductFeed\Model\Profile');
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This record no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        
        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('profile', $model);
        
        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Profile') : __('New Profile'),
            $id ? __('Edit Profile') : __('New Profile')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Profile'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __("Edit Profile") : __('New Profile'));
        
        return $resultPage;
    }
}