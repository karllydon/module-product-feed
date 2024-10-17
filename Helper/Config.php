<?php

namespace VaxLtd\ProductFeed\Helper;


use Magento\Framework\App\Config\ScopeConfigInterface;



class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_PRODUCT_FEED_ENABLED = "vaxltd_productfeed/general/enabled";

    const XML_PATH_PRODUCT_FEED_LOCAL_SAVE_PATH = "vaxltd_productfeed/general/local_save_path";

    const XML_PATH_PRODUCT_FEED_SFTP_HOST = "vaxltd_productfeed/general/sftp_host";
    const XML_PATH_PRODUCT_FEED_SFTP_PORT = "vaxltd_productfeed/general/sftp_port";
    const XML_PATH_PRODUCT_FEED_SFTP_USER = "vaxltd_productfeed/general/sftp_username";
    const XML_PATH_PRODUCT_FEED_SFTP_PASS = "vaxltd_productfeed/general/sftp_password";
    const XML_PATH_PRODUCT_FEED_SFTP_DEST_PATH = "vaxltd_productfeed/general/sftp_dest_path";



    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_FEED_ENABLED);
    }

    public function getLocalSavePath()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_FEED_LOCAL_SAVE_PATH);
    }

    public function getSftpHost()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_FEED_SFTP_HOST);
    }
    public function getSftpPort()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_FEED_SFTP_PORT);
    }
    public function getSftpUser()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_FEED_SFTP_USER);
    }
    public function getSftpPass()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_FEED_SFTP_PASS);
    }

    public function getSftpDestPath()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_FEED_SFTP_DEST_PATH);
    }
}
