<?php

namespace VaxLtd\ProductFeed\Model\Format;

interface FormatInterface
{
    /**
     * 
     * @param array $rows
     * @return bool|resource
     */
    public function formatFile($rows);
}