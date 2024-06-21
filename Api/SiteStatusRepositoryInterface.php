<?php

namespace OuterEdge\Base\Api;

interface SiteStatusRepositoryInterface
{
    /**
     * @return mixed[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData();
}
