<?php

namespace Mage42\PasswordSecurity\Plugin\Customer\Controller\Account;

use Magento\Customer\Controller\Account\ResetPasswordPost;
use Magento\Framework\Message\ManagerInterface;
use Magento\framework\Session\SessionManagerInterface;
use Magento\Framework\Message\MessageInterface;

class ResetPasswordPostPlugin
{
    protected $session;
    protected $messageManager;

    public function __construct(
        SessionManagerInterface $session,
        ManagerInterface $messageManager
    ) {
        $this->session = $session;
        $this->messageManager = $messageManager;
    }

    public function afterExecute(ResetPasswordPost $subject, $result) {
        if ($this->session->getSuppressSuccessMessage()) {
            $this->clearSuccessMessages();
            $this->session->unsSuppressSuccessMessage();
        }

        return $result;
    }

    private function clearSuccessMessages() {
       $messageCollection = $this->messageManager->getMessages();
        foreach ($messageCollection->getItemsByType(MessageInterface::TYPE_SUCCESS) as $successMessage) {
            $messageCollection->deleteMessageByIdentifier($successMessage->getIdentifier());
        }
    }
}

