<?php

namespace Mage42\PasswordSecurity\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;

class Prtct implements DataPatchInterface
{
    /**
     * @var ResourceConfig
     */
    private $resourceConfig;

    /**
     * Constructor
     */
    public function __construct(ResourceConfig $resourceConfig)
    {
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * Apply patch to set default configuration value
     */
    public function apply()
    {
        $this->resourceConfig->saveConfig(
            'passwordsecurity/notifications/userpass_failure_message',
            'The entered email/ password combination was found in a list of leaked user information. Please use a different password.',
            'default',
            0
        );

        $this->resourceConfig->saveConfig(
            'passwordsecurity/notifications/pass_failure_message',
            'The entered password was found in a list of leaked user information. Please use a different password.',
            'default',
            0
        );

        $this->resourceConfig->saveConfig(
            'passwordsecurity/notifications/userpass_warning_message',
            'The entered email/ password combination was found in a list of leaked user information. Please consider using a different password.',
            'default',
            0
        );

        $this->resourceConfig->saveConfig(
            'passwordsecurity/notifications/pass_warning_message',
            'The entered password was found in a list of leaked user information. Please consider using a different password.',
            'default',
            0
        );

        $this->resourceConfig->saveConfig(
            'passwordsecurity/general/http_timeout',
            '1',
            'default',
            0
        );
    }

    /**
     * Get patch dependencies
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get patch aliases
     */
    public function getAliases()
    {
        return [];
    }
}

