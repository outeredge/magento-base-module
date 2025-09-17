<?php

namespace OuterEdge\Base\Plugin;

use AuroraExtensions\GoogleCloudStorage\Model\File\Storage as Gcs;
use Magento\Csp\Api\PolicyCollectorInterface;
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
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
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

        switch ($cmpProvider) {
            case CmpProvider::CMP_COOKIEBOT:
                $cmpWhitelist = "*.cookiebot.com";
                break;
            case CmpProvider::CMP_TERMLY:
                $cmpWhitelist = "*.termly.io";
                break;
            default:
                $cmpWhitelist = null;
                break;
        }

        if ($cmpWhitelist) {
            $result[] = new FetchPolicy(
                'connect-src',
                false,
                [$cmpWhitelist]
            );

            $result[] = new FetchPolicy(
                'script-src',
                false,
                [$cmpWhitelist]
            );

            $result[] = new FetchPolicy(
                'img-src',
                false,
                [$cmpWhitelist]
            );

            $result[] = new FetchPolicy(
                'frame-src',
                false,
                [$cmpWhitelist]
            );
        }

        $mediaStorage = $this->scopeConfig->getValue(
            Storage::XML_PATH_STORAGE_MEDIA,
            ScopeInterface::SCOPE_STORE
        );

        if ($mediaStorage == Gcs::STORAGE_MEDIA_GCS) {
            $result[] = new FetchPolicy(
                'img-src',
                false,
                ['*.cdn.edge-servers.com']
            );
        }

        return $result;
    }
}
