<?php

namespace OuterEdge\Base\Framework\App\Encryption;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Encryption\KeyValidator;
use Magento\Framework\Math\Random;

/**
 * Forces Magento to use the first encryption key for media paths to avoid regenerating product media when we invalidate the previous encryption key
 */
class FirstKeyForMediaEncryptor extends Encryptor
{
    public function __construct(
        Random $random,
        DeploymentConfig $deploymentConfig,
        ?KeyValidator $keyValidator = null
    ) {
        parent::__construct($random, $deploymentConfig, $keyValidator);

        $this->keyVersion = 0;
    }

    public function encrypt($data)
    {
        throw new \LogicException('This encryptor is only for use with media assets');
    }

    public function decrypt($data)
    {
        throw new \LogicException('This encryptor is only for use with media assets');
    }
}