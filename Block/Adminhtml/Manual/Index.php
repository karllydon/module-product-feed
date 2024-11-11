<?php

namespace VaxLtd\ProductFeed\Block\Adminhtml\Manual;


use VaxLtd\ProductFeed\Model\Config\Source\Profiles;

class Index extends \Magento\Backend\Block\Template
{
    protected $_template = 'manual_form.phtml';

    /**
     * @var Profiles
     */
    protected $profiles;

    public function __construct(
        Profiles $profiles,
        \Magento\Backend\Block\Template\Context $context
    ) {
        $this->profiles = $profiles;
        parent::__construct($context);
    }

    public function getProfiles()
    {
        return $this->profiles->toOptionArray();
    }


}
