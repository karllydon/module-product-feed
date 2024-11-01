<?php

namespace VaxLtd\ProductFeed\Model\ExportMethod;


class Local extends AbstractClass
{

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem = null;

    /**
     * @throws \Exception
     * @return bool
     */
    public function testConnection()
    {
        try {
            $filepath = $this->config->getLocalSavePath();
            $exportDirectory = $this->fixBasePath($filepath);
            // Check for forbidden folders
            $forbiddenFolders = [
                $this->filesystem->getDirectoryRead(
                    \Magento\Framework\App\Filesystem\DirectoryList::ROOT
                )->getAbsolutePath()
            ];
            foreach ($forbiddenFolders as $forbiddenFolder) {
                if (realpath($exportDirectory) == $forbiddenFolder) {
                    throw new \Exception('It is not allowed to save export files in the directory you have specified. Please change the local export directory to be located in the ./var/ folder for example. Do not use the Magento root directory for example.');
                }
            }

            if (!file_exists($filepath)) {
                $mkdirResult = mkdir($filepath, 0755, true);
                if (!$mkdirResult) throw new \Exception('The specified local directory does not exist. We could not create it either. Please make sure the parent directory is writable or create the directory manually');
            }
            
            $connectionResult = opendir($exportDirectory);
            
            if (!$connectionResult || !is_writable($exportDirectory)){
                throw new \Exception(" 'Could not open local export directory for writing. Please make sure that we have rights to read and write in the directory");
            }
            else return true;
        } catch (\Exception $e) {
            $this->errorMsg = $e->getMessage();
            return false;
        }
    }

    /**
     * @param \VaxLtd\ProductFeed\Model\Profile $profile
     * @throws \VaxLtd\ProductFeed\Model\Destination $destination
     * @return bool
     */
    public function uploadFile($profile, $destination)
    {

        $this->filesystem = $this->objectManager->create('\Magento\Framework\Filesystem');
        $start = time();
        $end = 0;
        try {
            $connectTest = $this->testConnection();
            if (!$connectTest) {
                throw new \Exception($this->errorMsg);
            }
            $exportDirectory = $this->fixBasePath($profile->getPath());
            $target = "{$exportDirectory}{$profile->getFilename()}.{$profile->getFormat()}";
            $fp = fopen($target, 'w');
            foreach ($this->rows as $product) {
                fputcsv($fp, $product);
            }
            fclose($fp);
            $end = time();
            $this->exportSuccess = true;
        } catch (\Exception $e) {
            $this->errorMsg = "Product feed local save error {$e->getMessage()}";
            $this->logger->error($this->errorMsg);
        }
        finally {
            $this->dispatchExportEvent(['type' => 'local' , 'records' => count($this->rows) - 1, 'errorMsg' => $this->errorMsg, 'duration' => $end - $start]);
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