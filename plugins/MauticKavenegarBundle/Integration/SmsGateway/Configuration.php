<?php

namespace MauticPlugin\MauticKavenegarBundle\Integration\SmsGateway;

use Mautic\CoreBundle\Helper\EncryptionHelper;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticKavenegarBundle\Integration\Exceptions\SmsGatewayException;
use MauticPlugin\MauticKavenegarBundle\Integration\MauticKavenegarIntegration;

class Configuration
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var EncryptionHelper
     */
    private $encryptionHelper;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $fromNumber;

    /**
     * @var string
     */
    private $publicApiPath;
    

    /**
     * @var string
     */
    private $unsubscribeRegexPattern;


    /**
     * @var string
     */
    private $unsubscribedMessage;

    public function __construct(IntegrationHelper $integrationHelper, EncryptionHelper $encryptionHelper)
    {
        $this->integrationHelper = $integrationHelper;
        $this->encryptionHelper = $encryptionHelper;
    }

    /**
     * @return string
     * 
     * @throws SmsGatewayException
     */
    public function getApiKey(): string
    {
        $this->loadConfigurationIfNeeded();
        
        return $this->apiKey;
    }

    /**
     * @return string
     * 
     * @throws SmsGatewayException
     */
    public function getFromNumber(): string
    {
        $this->loadConfigurationIfNeeded();
        
        return $this->fromNumber;
    }

    /**
     * @return string
     * 
     * @throws SmsGatewayException
     */
    public function getPublicApiPath(): string
    {
        $this->loadConfigurationIfNeeded();
        
        return $this->publicApiPath;
    }

    
    /**
     * @return string
     * 
     * @throws SmsGatewayException
     */
    public function getUnsubscribeRegexPattern(): string
    {
        $this->loadConfigurationIfNeeded();
        
        return $this->unsubscribeRegexPattern;
    }

        /**
     * @return string
     * 
     * @throws SmsGatewayException
     */
    public function getUnsubscribedMessage(): string
    {
        $this->loadConfigurationIfNeeded();
        
        return $this->unsubscribedMessage;
    }

    /**
     * @throws SmsGatewayException
     */
    private function loadConfigurationIfNeeded(): void
    {
        // if ($this->apiKey) {
        //     return;
        // }

        $integration = $this->integrationHelper->getIntegrationObject(MauticKavenegarIntegration::NAME);

        if (!$integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            throw new SmsGatewayException("shittt");
        }
        
        $keys = $this->decryptApiKeys($integration->getIntegrationSettings()->getApiKeys());
        if (empty($keys['apiKey'])) {
            throw new SmsGatewayException();
        }

        $this->apiKey = $keys['apiKey'];
        $this->fromNumber = $keys['fromNumber'];
        $this->unsubscribeRegexPattern = $keys['unsubscribeRegexPattern'];
        $this->unsubscribedMessage = $keys['unsubscribedMessage'];
        $this->publicApiPath = $keys['publicApiPath'];
    }

    /**
     * @param array $keys
     * 
     * @return array
     */
    private function decryptApiKeys(array $keys): array
    {
        $decrypted = [];

        foreach ($keys as $name => $key) {
            $key = $this->encryptionHelper->decrypt($key);
            if (false === $key) {
                return [];
            }
            $decrypted[$name] = $key;
        }

        return $decrypted;
    }
}