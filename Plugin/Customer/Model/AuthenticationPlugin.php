<?php

namespace Mage42\PasswordSecurity\Plugin\Customer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Authentication;
use Mage42\PasswordSecurity\Helper\PasswordCheck;

class AuthenticationPlugin
{
    private $passwordCheck;
    protected $customerRepository;

    public function __construct(
        PasswordCheck $passwordCheck,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->passwordCheck = $passwordCheck;
        $this->customerRepository = $customerRepository;
    }
    public function aroundAuthenticate(Authentication $subject, callable $proceed, $customerId, $password) {
        $customer = $this->customerRepository->getById($customerId);
        $customerEmail = $customer->getEmail();
        $continueAfter = false;
        //$this->passwordCheck->checkUserPassHash($customerEmail, $password, $continueAfter);
        //$this->passwordCheck->checkPassHash($password, $continueAfter);

        return $proceed($customerId, $password);
    }
}
