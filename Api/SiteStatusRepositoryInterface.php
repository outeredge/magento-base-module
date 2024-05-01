<?php

namespace OuterEdge\Base\Api;

interface SiteStatusRepositoryInterface
{
    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData();
}
