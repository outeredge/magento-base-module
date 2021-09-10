<?php

namespace OuterEdge\Base\Framework\App\Cache;

use Magento\Framework\App\Cache\State as MagentoState;

/**
 * Prevent Magento from writing to env.php
 */
class State extends MagentoState
{
    public function persist() {}
}
