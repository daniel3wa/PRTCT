<?php

namespace Mage42\PasswordSecurity\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class CheckConnection extends Action
{
    protected $resultJsonFactory;
    protected $scopeConfig;
    protected $curl;
    protected $logger;

    const API_HEALTH_URL = 'https://api.3webapps.com/api/v1/health/check';
    const TIMEOUT = 1; // Timeout in seconds

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->curl = $curl;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $apiKey = $this->scopeConfig->getValue('passwordsecurity/general/api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $apiBearerToken = 'Bearer ' . $apiKey;

        $this->curl->setHeaders(['Authorization' => $apiBearerToken]);
        $this->curl->setTimeout(self::TIMEOUT);

        try {
            $this->curl->get(self::API_HEALTH_URL);
            $response = $this->curl->getBody();
            $responseData = json_decode($response, true);

            if ($responseData && isset($responseData['health']) && $responseData['health'] === 'ok') {
                return $result->setData(['success' => true, 'message' => 'API key is valid']);
            } else {
                return $result->setData(['success' => false, 'message' => 'API key is invalid (did you save the config?)']);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error checking API key: ' . $e->getMessage());
            return $result->setData(['success' => false, 'message' => 'API key validation failed']);
        }
    }
}
