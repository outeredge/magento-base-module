<?php
declare(strict_types=1);

namespace OuterEdge\Base\Controller\Result;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Store\Model\ScopeInterface;
use OuterEdge\Base\Model\Config\Source\CmpProvider;

/**
 * Plugin for putting all JavaScript tags to the end of body.
 */
class JsFooterPlugin
{
    const XML_PATH_CMPPROVIDER = 'oe_base/cmp/provider';

    private const XML_PATH_DEV_MOVE_JS_TO_BOTTOM = 'dev/js/move_script_to_bottom';

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * Moves all JavaScript tags to the end of body if this feature is enabled.
     *
     * @param Layout $subject
     * @param Layout $result
     * @param HttpResponseInterface|ResponseInterface $httpResponse
     * @return Layout (That should be void, actually)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderResult(Layout $subject, Layout $result, ResponseInterface $httpResponse)
    {
        if (!$this->isDeferEnabled()) {
            return $result;
        }

        $content = (string)$httpResponse->getContent();
        $bodyEndTag = '</body';
        $bodyEndTagFound = strrpos($content, $bodyEndTag) !== false;

        if ($bodyEndTagFound) {
            if ($this->getCmpPlatform() !== null) {
                $content = $this->applyIframeCookieRestriction($content, 'youtube');
                $content = $this->applyLiteYouTubeCookieRestriction($content, 'lite-youtube');
            }
            $scripts = $this->extractScriptTags($content);
            if ($scripts) {
                $newBodyEndTagPosition = strrpos($content, $bodyEndTag);
                $content = substr_replace($content, $scripts . "\n", $newBodyEndTagPosition, 0);
                $httpResponse->setContent($content);
            }
        }

        return $result;
    }

    /**
     * Extracts and returns script tags found in given content.
     *
     * @param string $content
     */
    private function extractScriptTags(&$content): string
    {
        $scripts = '';
        $scriptOpen = '<script';
        $scriptClose = '</script>';
        $scriptOpenPos = strpos($content, $scriptOpen);

        while ($scriptOpenPos !== false) {
            $scriptClosePos = strpos($content, $scriptClose, $scriptOpenPos);
            $script = substr($content, $scriptOpenPos, $scriptClosePos - $scriptOpenPos + strlen($scriptClose));
            $isXMagentoTemplate = strpos($script, 'text/x-magento-template') !== false;

            if ($isXMagentoTemplate) {
                $scriptOpenPos = strpos($content, $scriptOpen, $scriptClosePos);
                continue;
            }

            //outer/edge skip Lazysizes & Cookiebot
            $skipScript = (str_contains($script, 'lazysizes') || str_contains($script, 'cookiebot') || str_contains($script, 'termly') || str_contains($script, 'CookieDeclaration'));

            if ($skipScript) {
                $scriptOpenPos = strpos($content, $scriptOpen, $scriptClosePos);
                continue;
            }
            //outer/edge skip Lazysizes & Cookiebot

            $scripts .= "\n" . $script;
            $content = str_replace($script, '', $content);
            // Script cut out, continue search from its position.
            $scriptOpenPos = strpos($content, $scriptOpen, $scriptOpenPos);
        }

        return $scripts;
    }

    /**
     * Returns information whether moving JS to footer is enabled
     *
     * @return bool
     */
    private function isDeferEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEV_MOVE_JS_TO_BOTTOM,
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getCmpPlatform(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CMPPROVIDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * outer/edge
     * https://support.cookiebot.com/hc/en-us/articles/360003790854-Iframe-cookie-consent-with-YouTube-example
     *
     * @param string $content
     */
    private function applyIframeCookieRestriction(&$content, $srcContains): string
    {
        $iframeOpen = '<iframe';
        $iframeClose = '</iframe>';
        $iframeOpenPos = strpos($content, $iframeOpen);

        while ($iframeOpenPos !== false) {
            $scriptClosePos = strpos($content, $iframeClose, $iframeOpenPos);
            $iframe = substr($content, $iframeOpenPos, $scriptClosePos - $iframeOpenPos + strlen($iframeClose));

            $skipScript = (str_contains($iframe, 'data-cookieblock-src') || str_contains($iframe, $srcContains));

            if ($skipScript) {
                $iframeOpenPos = strpos($content, $iframeOpen, $scriptClosePos);
                continue;
            }

            if ($this->getCmpPlatform() == CmpProvider::CMP_COOKIEBOT) {
                $newIframe = str_replace(' src=', ' data-cookieconsent="marketing" data-cookieblock-src=', $iframe);
            } elseif ($this->getCmpPlatform() == CmpProvider::CMP_TERMLY) {
                $newIframe = str_replace(' src=', ' data-categories="advertising" data-src=', $iframe);
            }

            $content = str_replace($iframe, $newIframe, $content);
            $iframeOpenPos = strpos($content, $iframeOpen); // get new open pos with updated content
            // Script cut out, continue search from its position.
            $iframeOpenPos = strpos($content, $iframeOpen, $iframeOpenPos);
        }

        return $content;
    }

    private function applyLiteYouTubeCookieRestriction(&$content): string
    {
        $elOpen = '<lite-youtube';
        $elClose = '</lite-youtube>';
        $elOpenPos = strpos($content, $elOpen);

        while ($elOpenPos !== false) {
            $scriptClosePos = strpos($content, $elClose, $elOpenPos);
            $element = substr($content, $elOpenPos, $scriptClosePos - $elOpenPos + strlen($elClose));

            $skipScript = str_contains($element, 'cookieconsent-optin-marketing');

            if ($skipScript) {
                $elOpenPos = strpos($content, $elOpen, $scriptClosePos);
                continue;
            }

            if ($this->getCmpPlatform() == CmpProvider::CMP_COOKIEBOT) {
                $newElement = str_replace('<lite-youtube', '<lite-youtube class="cookieconsent-optin-marketing"', $element);
            } elseif ($this->getCmpPlatform() == CmpProvider::CMP_TERMLY) {
                $newElement = str_replace('<lite-youtube', '<lite-youtube data-categories="advertising"', $element);
            }

            $content = str_replace($element, $newElement, $content);
            $elOpenPos = strpos($content, $elOpen); // get new open pos with updated content
            // Script cut out, continue search from its position.
            $elOpenPos = strpos($content, $elOpen, $elOpenPos);
        }

        return $content;
    }
}
