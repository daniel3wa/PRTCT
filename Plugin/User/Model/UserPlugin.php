<?php

namespace Mage42\PasswordSecurity\Plugin\User\Model;

use Magento\User\Model\User;
use Mage42\PasswordSecurity\Helper\PasswordCheck;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Message\Error;

class UserPlugin
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

    public function aroundVerifyIdentity(User $subject, callable $proceed, $password) {
        $email = $subject->getEmail();
        try {
            if ($this->passwordCheck->checkUserPassHash($email, $password) &&
                $this->passwordCheck->checkPassHash($password)) {
            return $proceed($password);
            }
        } catch (\Exception $e) {
            $this->messageManager->addMessage(new Error($e->getMessage(), "prtct_error"));
            $this->session->setSuppressSuccessMessage(true);
            return;
        }
        return $proceed($password);
    }

    public function aroundValidate(User $subject, callable $proceed) {
        $email = $subject->getEmail();
        $password = $subject->getPassword();
        try {
            if ($this->passwordCheck->checkUserPassHash($email, $password) &&
                $this->passwordCheck->checkPassHash($password)) {
                return $proceed();
            }
        } catch (\Exception $e) {
            $this->messageManager->addMessage(new Error($e->getMessage(), "prtct_error"));
            $this->session->setSuppressSuccessMessage(true);
            return;
        }
        return $proceed();
    }
}
