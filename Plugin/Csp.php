<?php

namespace OuterEdge\Base\Plugin;

use AuroraExtensions\GoogleCloudStorage\Model\File\Storage as Gcs;
use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\Collector\MergerInterface;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\MediaStorage\Model\File\Storage;
use Magento\Store\Model\ScopeInterface;
use OuterEdge\Base\Model\Config\Source\CmpProvider;

/**
 * Plugin to conditionally add CSP policies based on configuration
 */
class Csp
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var MergerInterface
     */
    private $merger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param MergerInterface $merger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        MergerInterface $merger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->merger = $merger;
    }

    /**
     * Add Termly frame-src policy if configuration is enabled
     *
     * @param PolicyCollectorInterface $subject
     * @param array $result
     * @return array
     */
    public function afterCollect(PolicyCollectorInterface $subject, array $result): array
    {
        $cmpProvider = $this->scopeConfig->getValue(
            'oe_base_cookierestriction/cmp/provider',
            ScopeInterface::SCOPE_STORE
        );

        $cmpWhitelist = null;
        switch ($cmpProvider) {
            case CmpProvider::CMP_COOKIEBOT:
                $cmpWhitelist = "*.cookiebot.com";
                break;
            case CmpProvider::CMP_TERMLY:
                $cmpWhitelist = "*.termly.io";
                break;
        }

        if ($cmpWhitelist) {
            $policies = [
                new FetchPolicy('connect-src', false, [$cmpWhitelist], [], true),
                new FetchPolicy('script-src', false, [$cmpWhitelist], [], true),
                new FetchPolicy('img-src', false, [$cmpWhitelist], [], true),
                new FetchPolicy('frame-src', false, [$cmpWhitelist], [], true)
            ];
            $result = $this->mergePolicies($result, $policies);
        }

        $mediaStorage = $this->scopeConfig->getValue(
            Storage::XML_PATH_STORAGE_MEDIA,
            ScopeInterface::SCOPE_STORE
        );

        if ($mediaStorage == Gcs::STORAGE_MEDIA_GCS) {
            $policies = [new FetchPolicy('img-src', false, ['cdn.edge-servers.com'], [], true)];
            $result = $this->mergePolicies($result, $policies);
        }

        return $result;
    }

    /**
     * @param array $original
     * @param array $newPolicies
     * @return array
     */
    private function mergePolicies(array $original, array $newPolicies): array
    {
        $originalById = [];
        foreach ($original as $policy) {
            $originalById[$policy->getId()] = $policy;
        }

        foreach ($newPolicies as $newPolicy) {
            $id = $newPolicy->getId();
            if (isset($originalById[$id])) {
                if ($this->merger->canMerge($originalById[$id], $newPolicy)) {
                    $originalById[$id] = $this->merger->merge($originalById[$id], $newPolicy);
                }
            } else {
                $originalById[$id] = $newPolicy;
            }
        }
        return array_values($originalById);
    }
}
