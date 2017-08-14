<?php

namespace OuterEdge\Base\Plugin\Framework\App;

use Magento\Framework\App\Config as AppConfig;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\Store;
use Magento\Backend\App\Area\FrontNameResolver;

class Config
{
    /**
     * @var State
     */
    protected $_state;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var bool
     */
    protected $_isDeveloperMode;

    /**
     * @var string
     */
    protected $_httpHost;

    /**
     * @param State $state
     */
    public function __construct(
        State $state,
        Filesystem $filesystem
    ) {
        $this->_state = $state;
        $this->_filesystem = $filesystem;
    }

    public function aroundGet(AppConfig $subject, callable $proceed, $configType, $path = '', $default = null)
    {
        if ($this->isDeveloperMode()) {
            switch (true) {
                case stristr($path, Store::XML_PATH_SECURE_IN_FRONTEND):
                case stristr($path, Store::XML_PATH_SECURE_IN_ADMINHTML):
                case stristr($path, FrontNameResolver::XML_PATH_USE_CUSTOM_ADMIN_URL):
                    return null;

                case stristr($path, Store::XML_PATH_SECURE_BASE_URL):
                case stristr($path, Store::XML_PATH_UNSECURE_BASE_URL):
                case stristr($path, Store::XML_PATH_SECURE_BASE_LINK_URL):
                case stristr($path, Store::XML_PATH_UNSECURE_BASE_LINK_URL):
                    return 'http://' . $this->getHttpHost($proceed) . '/';

                case stristr($path, Store::XML_PATH_SECURE_BASE_STATIC_URL):
                case stristr($path, Store::XML_PATH_UNSECURE_BASE_STATIC_URL):
                    return 'http://' . $this->getHttpHost($proceed) . '/' . $this->_filesystem->getUri(DirectoryList::STATIC_VIEW);

                case stristr($path, Store::XML_PATH_SECURE_BASE_MEDIA_URL):
                case stristr($path, Store::XML_PATH_UNSECURE_BASE_MEDIA_URL):
                    return 'http://' . $this->getHttpHost($proceed) . '/' . $this->_filesystem->getUri(DirectoryList::MEDIA);

                default:
                    return $proceed($configType, $path, $default);
            }
        }
        return $proceed($configType, $path, $default);
    }

    protected function isDeveloperMode()
    {
        if (!$this->_isDeveloperMode) {
            $this->_isDeveloperMode = $this->_state->getMode() === State::MODE_DEVELOPER;
        }
        return $this->_isDeveloperMode;
    }

    protected function getHttpHost($proceed)
    {
        if (!$this->_httpHost) {
            if (isset($_SERVER['HTTP_HOST'])) {
                $this->_httpHost = $_SERVER['HTTP_HOST'];
            } else {
                $dbBaseUrl = $proceed('system', 'default/' . Store::XML_PATH_UNSECURE_BASE_URL);
                $this->_httpHost = parse_url(trim($dbBaseUrl), PHP_URL_HOST);
            }
        }
        return $this->_httpHost;
    }
}
