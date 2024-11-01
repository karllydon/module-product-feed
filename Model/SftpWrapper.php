<?php

namespace VaxLtd\ProductFeed\Model;

use VaxLtd\ProductFeed\Helper\Config;
use VaxLtd\ProductFeed\Logger\Logger;
use Magento\Framework\Filesystem\Io\Sftp;

class SftpWrapper
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;


    /**
     * @var Sftp
     */
    protected $sftp;

    public function __construct(Config $config, Logger $logger, Sftp $sftp)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->sftp = $sftp;
    }

    protected function connect()
    {
        $this->sftp->open(["host" => $this->config->getSftpHost() . ":" . $this->config->getSftpPort(), "username" => $this->config->getSftpUser(), "password" => $this->config->getSftpPass()]);
    }

    protected function close()
    {
        $this->sftp->close();
    }

    public function testConnection()
    {
        try {
            $this->connect();
            $this->logger->debug("SFTP connected, pwd: " . $this->sftp->pwd());
            $this->logger->debug("ls: " . print_r($this->sftp->ls(), true));
            $this->close();
            return true;
        } catch (\Exception $e) {
            $this->logger->debug("Could not establish sftp connection {$e}");
            return false;
        }
    }

    /**
     * @param string $fileName
     * @param string $file
     * @return bool
     */
    public function uploadFile($fileName, $file)
    {
        try {
            $this->connect();
            $this->logger->debug("uploading File");
            $this->sftp->write($this->config->getSftpDestPath() . basename($fileName), $file);
            $this->close();
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Product feed sftp error {$e}");
            return false;
        }
    }
}
