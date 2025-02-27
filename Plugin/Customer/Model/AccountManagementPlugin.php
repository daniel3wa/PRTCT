<?php

namespace Mage42\PasswordSecurity\Plugin\Customer\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Mage42\PasswordSecurity\Helper\PasswordCheck;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Message\Error;

class AccountManagementPlugin
{
    protected $passwordCheck;
    protected $messageManager;
    protected $session;

    public function __construct(
        PasswordCheck $passwordCheck,
        ManagerInterface $messageManager,
        SessionManagerInterface $session,
    ) {
        $this->passwordCheck = $passwordCheck;
        $this->messageManager = $messageManager;
        $this->session = $session;
    }

    public function aroundResetPassword(AccountManagement $subject, callable $proceed, $email, $resetToken, $newPassword) {
        try {
            if ($this->passwordCheck->checkUserPassHash($email, $newPassword) &&
                    $this->passwordCheck->checkPassHash($newPassword)) {
                    return $proceed($email, $resetToken, $newPassword);
            }
        } catch (\Exception $e) {
            $this->messageManager->addMessage(new Error($e->getMessage(), "prtct_error"));
            $this->session->setSuppressSuccessMessage(true);
            return;
        }
        return $proceed($email, $resetToken, $newPassword);
    }

    public function aroundChangePassword(AccountManagement $subject, callable $proceed, $email, $currentPassword, $newPassword) {
        try {
            if ($this->passwordCheck->checkUserPassHash($email, $newPassword) &&
                    $this->passwordCheck->checkPassHash($newPassword)) {
                return $proceed($email, $currentPassword, $newPassword);
            }
        } catch (\Exception $e) {
            $this->messageManager->addMessage(new Error($e->getMessage(), "prtct_error"));
            $this->session->setSuppressSuccessMessage(true);
            return;
        }
        return $proceed($email, $currentPassword, $newPassword);
    }

    public function aroundAuthenticate(AccountManagement $subject, callable $proceed, $username, $password) {
        try {
            if ($this->passwordCheck->checkUserPassHash($username, $password, true) &&
                    $this->passwordCheck->checkPassHash($password, true)) {
                return $proceed($username, $password);
            }
        } catch (\Exception $e) {
            // WE NEED TO TPHROW EXCEPTION HERE
            // see loginPost.php:192
            $this->messageManager->addMessage(new Error($e->getMessage(), "prtct_error"));
            $this->session->setSuppressSuccessMessage(true);
            throw new LocalizedException(__($e->getMessage()));
            return;
        }
        return $proceed($username, $password);
    }

    public function aroundCreateAccount(AccountManagement $subject, callable $proceed,
        CustomerInterface $customer, $password = null, $redirectUrl = '') {
        $customerEmail = $customer->getEmail();
        try {
            if ($this->passwordCheck->checkUserPassHash($customerEmail, $password) &&
                    $this->passwordCheck->checkPassHash($password)) {
                return $proceed($customer, $password, $redirectUrl);
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
            $this->session->setSuppressSuccessMessage(true);
            return;
        }
        return $proceed($customer, $password, $redirectUrl);
    }
}
