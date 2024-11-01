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
     * @return bool
     */
    public function uploadFile($profile, $destination);
}