<?php

namespace OuterEdge\Base\Plugin\Framework\File\Transfer\Adapter;

use Magento\Framework\File\Transfer\Adapter\Http as MagentoHttp;
use Magento\Framework\HTTP\PhpEnvironment\Response;

/**
 * Send a 404 response code for placeholder images
 */
class Http
{
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function beforeSend(MagentoHttp $http, $options)
    {
        if (isset($options['filepath']) && stristr($options['filepath'], 'placeholder')) {
            $this->response->setHttpResponseCode(404);
        }

        return $options;
    }
}
