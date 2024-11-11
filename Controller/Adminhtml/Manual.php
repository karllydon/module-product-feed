<?php

namespace VaxLtd\ProductExport\Controller\Adminhtml;

class Manual 
{

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * @param $resultPage \Magento\Backend\Model\View\Result\Page
     */
    protected function updateMenu($resultPage)
    {
        $resultPage->setActiveMenu('VaxLtd_ProductFeed::manual');
        $resultPage->addBreadcrumb(__('Products'), __('Products'));
        $resultPage->addBreadcrumb(__('Manual Export'), __('Manual Export'));
        $resultPage->getConfig()->getTitle()->prepend(__('Product Export - Manual Export'));
    }
}