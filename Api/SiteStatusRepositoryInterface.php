<?php

namespace OuterEdge\Base\Api;

interface SiteStatusRepositoryInterface
{
    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIndexer();

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfigs();
}
