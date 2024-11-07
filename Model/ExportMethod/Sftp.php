<?php

namespace VaxLtd\ProductFeed\Model\ExportMethod;

use Magento\Framework\Filesystem\Io\Sftp as MSftp;

class Sftp extends AbstractClass
{

    protected $host = null;
    protected $port = null;
    protected $username = null;
    protected $password = null;

    protected $timeout = null;




    /**
     * @var MSftp
     */
    protected $sftp;

    /**
     * @return bool
     */
    public function testConnection()
    {
        try {
            $connectParams = ["host" => "{$this->host}:{$this->port}", "username" => $this->username, "password" => $this->password];
            if ($this->timeout) {
                $connectParams["timeout"] = $this->timeout;
            }
            $this->sftp->open($connectParams);
            $this->logger->debug("SFTP connected, pwd: " . $this->sftp->pwd());
            $this->logger->debug("ls: " . print_r($this->sftp->ls(), true));
            return true;
        } catch (\Exception $e) {
            $this->errorMsg = "Could not establish sftp connection {$e->getMessage()}";
            $this->logger->error($this->errorMsg);

            return false;
        }
    }

    /**
     * @param \VaxLtd\ProductFeed\Model\Profile $profile
     * @param \VaxLtd\ProductFeed\Model\Destination $destination
     * @param int $count
     * @param resource $file
     * @return boolean
     */
    public function uploadFile($profile, $destination, $count, $file)
    {
        $this->sftp = $this->objectManager->create("Magento\Framework\Filesystem\Io\Sftp");
        $this->host = $destination->getHostname();
        $this->port = $destination->getPort();
        $this->username = $destination->getUsername();
        $this->password = $destination->getPassword();

        
        if ($destination->getTimeout()) {
            $this->timeout = $destination->getTimeout();
        }

        $start = time();
        $end = 0;
        try {
            $connectTest = $this->testConnection();

            if (!$connectTest) {
                return false;
            }

            $this->sftp->write("{$destination->getPath()}{$profile->getFilename()}.{$profile->getOutputType()}", rtrim(stream_get_contents($file), "\n"));
            $end = time();
            $this->duration = $end - $start;
            $this->successMsg = "Exported {$count} products in {$this->duration} seconds"; 
            $this->exportSuccess = true;
        } catch (\Exception $e) {
            $this->errorMsg = "Product feed sftp error {$e->getMessage()}";
            $this->logger->error($this->errorMsg);
        } finally {
            $this->sftp->close();
            return $this->exportSuccess;
        }
    }
}