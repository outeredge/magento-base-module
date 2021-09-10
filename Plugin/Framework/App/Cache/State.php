<?php

namespace OuterEdge\Base\Plugin\Framework\App\Cache;

/**
 * Prevent Magento from writing to env.php
 */
class State
{
    public function aroundPersist() { }
}
