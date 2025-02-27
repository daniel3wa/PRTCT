<?php

namespace Mage42\PasswordSecurity\Block\Adminhtml\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;

class ValidateApiKeyButton extends Field
{
    protected $_template = 'Mage42_PasswordSecurity::system/config/validate_api_key_button.phtml';

    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getButtonUrl()
    {
        return $this->getUrl('passwordsecurity/system_config/checkconnection');
    }
}
