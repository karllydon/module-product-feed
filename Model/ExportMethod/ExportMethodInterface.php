<?php

namespace VaxLtd\ProductFeed\Model\ExportMethod;

interface ExportMethodInterface
{

    /**
     * @return bool
     */
    public function testConnection();

    /**
     * @param \VaxLtd\ProductFeed\Model\Profile $profile
     * @param \VaxLtd\ProductFeed\Model\Destination $destination
     * @param int $count
     * @param resource $file 
     * @return bool
     */
    public function uploadFile($profile, $destination, $count, $file);


    /**
     * @param int $destination_id
     * @param string $type
     * @param int $count
     * @return void
     */
    public function dispatchExportEvent($destination_id, $type, $count);

    /**
     * @return string|null
     */
    public function getErrorMsg();

    /**
     * @return string|null
     */
    public function getSuccessMsg();

}