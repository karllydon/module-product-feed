<?php

namespace VaxLtd\ProductFeed\Model\ExportMethod;


class Local extends AbstractClass
{

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem = null;


    /**
     * @var string
     */
    protected $exportDirectory;

    /**
     * @throws \Exception
     * @return bool
     */
    public function testConnection()
    {
        try {
            $this->exportDirectory = $this->fixBasePath($this->exportDirectory);
            // Check for forbidden folders
            $forbiddenFolders = [
                $this->filesystem->getDirectoryRead(
                    \Magento\Framework\App\Filesystem\DirectoryList::ROOT
                )->getAbsolutePath()
            ];
            foreach ($forbiddenFolders as $forbiddenFolder) {
                if (realpath($this->exportDirectory) == $forbiddenFolder) {
                    throw new \Exception('It is not allowed to save export files in the directory you have specified. Please change the local export directory to be located in the ./var/ folder for example. Do not use the Magento root directory for example.');
                }
            }

            if (!file_exists($this->exportDirectory)) {
                $mkdirResult = mkdir($this->exportDirectory, 0777, true);
                if (!$mkdirResult)
                    throw new \Exception('The specified local directory does not exist. We could not create it either. Please make sure the parent directory is writable or create the directory manually');
            }

            $connectionResult = opendir($this->exportDirectory);

            if (!$connectionResult || !is_writable($this->exportDirectory)) {
                throw new \Exception(" 'Could not open local export directory for writing. Please make sure that we have rights to read and write in the directory");
            } else
                return true;
        } catch (\Exception $e) {
            $this->errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * @param \VaxLtd\ProductFeed\Model\Profile $profile
     * @param \VaxLtd\ProductFeed\Model\Destination $destination
     *@param int $count 
     * @param resource file
     * @return bool
     */
    public function uploadFile($profile, $destination, $count, $file)
    {
        $this->exportDirectory = $destination->getPath();

        $this->filesystem = $this->objectManager->create('\Magento\Framework\Filesystem');
        $start = time();
        $end = 0;
        try {
            $connectTest = $this->testConnection();
            if (!$connectTest) {
                throw new \Exception($this->errorMsg);
            }
            $target = "{$this->exportDirectory}{$profile->getFilename()}.{$profile->getOutputType()}";
            $fp = fopen($target, 'w');
            fputs($fp, rtrim(stream_get_contents($file), "\n"));
            fclose($fp);
            $end = time();
            $this->duration = $end - $start;
            $this->exportSuccess = true;
            $this->successMsg = "Exported {$count} products in {$this->duration} seconds"; 
        } catch (\Exception $e) {
            $this->errorMsg = "Product feed local save error {$e->getMessage()}";
            $this->logger->error($this->errorMsg);
        } finally {
            return $this->exportSuccess;
        }
    }
    protected function fixBasePath($originalPath)
    {
        /*
         * Let's try to fix the import directory and replace the dot with the actual Magento root directory.
         * Why? Because if the cronjob is executed using the PHP binary a different working directory (when using a dot (.) in a directory path) could be used.
         * But Magento is able to return the right base path, so let's use it instead of the dot.
         */
        $originalPath = str_replace('/', DIRECTORY_SEPARATOR, $originalPath);
        if (substr($originalPath, 0, 2) == '.' . DIRECTORY_SEPARATOR) {
            return rtrim($this->filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::ROOT
            )->getAbsolutePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . substr($originalPath, 2);
        }
        return $originalPath;
    }
}