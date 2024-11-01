<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace VaxLtd\ProductFeed\Model\Config\Source;

class Destination implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => "local", 'label' => __('Local')],
            ['value' => "sftp", 'label' => __('SFTP')],
        ];
    }
    }
