<?php

namespace Mage42\PasswordSecurity\Helper;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\Error;
use Normalizer;

class PasswordCheck
{
    const API_URL = 'https://api.3webapps.com/api/v1/leaks/pass/check';
    const API_USERPASS_URL = 'https://api.3webapps.com/api/v1/leaks/userpass/check';
    const API_HEALTH_URL = 'https://api.3webapps.com/api/v1/health/check';

    protected $curl;
    protected $messageManager;
    protected $scopeConfig;
    protected $apiBearerToken;
    protected $redirect;
    protected $request;

    private $passError;
    private $userpassError;
    private $passWarning;
    private $userpassWarning;
    private $disallowLogin;
    private $httpTimeout;

    public function __construct(
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        Curl $curl,
        RequestInterface $request,
        RedirectInterface $redirect

    ) {
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->apiBearerToken = $this->getApiKey();
        $this->curl = $curl;
        $this->request = $request;
        $this->redirect = $redirect;

        $this->passError = $this->scopeConfig->getValue('passwordsecurity/notifications/pass_failure_message', ScopeInterface::SCOPE_STORE);
        $this->userpassError = $this->scopeConfig->getValue('passwordsecurity/notifications/userpass_failure_message', ScopeInterface::SCOPE_STORE);
        $this->passWarning = $this->scopeConfig->getValue('passwordsecurity/notifications/pass_warning_message', ScopeInterface::SCOPE_STORE);
        $this->userpassWarning = $this->scopeConfig->getValue('passwordsecurity/notifications/userpass_warning_message', ScopeInterface::SCOPE_STORE);
        $this->disallowLogin = $this->scopeConfig->getValue('passwordsecurity/notifications/disallow_login', ScopeInterface::SCOPE_STORE);
        $this->httpTimeout = $this->scopeConfig->getValue('passwordsecurity/general/http_timeout', ScopeInterface::SCOPE_STORE);
    }

    public function checkUserPassHash($email, $password, $login = false) {
        if ($email != null && $password != null) {
            $normalizedEmail = normalizer_normalize(strtolower($email), Normalizer::FORM_C);
            $normalizedPassword = normalizer_normalize($password, Normalizer::FORM_C);

            $hashedEmailPassword = hash('sha256', "$normalizedEmail$normalizedPassword");

            if ($this->checkApiHealth()) {
                if ($this->checkUserAndPasswordWithApi($hashedEmailPassword)) {
                    if($login && !$this->disallowLogin) {
                        $this->messageManager->addWarningMessage(__($this->userpassWarning));
                        return true;
                    } else {
                        throw new LocalizedException(__($this->userpassError));
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function checkPassHash($password, $login = false) {
        if ($password !== null) {
            $normalizedPassword = normalizer_normalize($password, Normalizer::FORM_C);
            $hashedPassword = hash('sha256', $normalizedPassword);

            if ($this->checkApiHealth()) {
                if ($this->checkPasswordWithApi($hashedPassword)) {
                    if($login && !$this->disallowLogin) {
                        $this->messageManager->addWarningMessage(__($this->passWarning));
                        return true;
                    } else {
                        throw new LocalizedException(__($this->passError));
                        return false;
                    }
                }
            }
        }
        return true;
    }

    private function checkApiHealth() {
        $httpClient = $this->curl;

        $httpClient->setHeaders(['Authorization' => 'Bearer ' . $this->apiBearerToken]);
        $httpClient->setTimeout($this->httpTimeout);

        try {
            $httpClient->get(self::API_HEALTH_URL);
            $response = $httpClient->getBody();
            $responseDecoded = json_decode($response, true);

            return isset($responseDecoded['health']) && $responseDecoded['health'] === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkPasswordWithApi($hashedPassword) {
        $httpClient = $this->curl;

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiBearerToken,
            'Content-Type' => 'application/json'
        ];

        $postData = json_encode(['passhash' => $hashedPassword]);

        $httpClient->setHeaders($headers);
        $httpClient->setTimeout($this->httpTimeout);

        try {
            $httpClient->post(self::API_URL, $postData);
            $response = $httpClient->getBody();
            $responseDecoded = json_decode($response, true);

            return isset($responseDecoded['hit']) && $responseDecoded['hit'] === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkUserAndPasswordWithApi($hashedEmailPassword) {
        $httpClient = $this->curl;

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiBearerToken,
            'Content-Type' => 'application/json'
        ];

        $postData = json_encode(['userpasshash' => $hashedEmailPassword]);

        $httpClient->setHeaders($headers);
        $httpClient->setTimeout($this->httpTimeout);

        try {
            $httpClient->post(self::API_USERPASS_URL, $postData);
            $response = $httpClient->getBody();
            $responseDecoded = json_decode($response, true);

            return isset($responseDecoded['hit']) && $responseDecoded['hit'] === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getApiKey() {
        return $this->scopeConfig->getValue('passwordsecurity/general/api_key', ScopeInterface::SCOPE_STORE);
    }
}
