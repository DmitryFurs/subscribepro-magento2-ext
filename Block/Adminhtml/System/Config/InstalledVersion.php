<?php

namespace Swarming\SubscribePro\Block\Adminhtml\System\Config;

class InstalledVersion extends \Magento\Config\Block\System\Config\Form\Field
{
    public const INSTALLED_VERSION = '1.7.0';

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setValue(self::INSTALLED_VERSION);

        return '<strong>'
            . $element->getEscapedValue()
            . '</strong> - [<a href="https://github.com/subscribepro/subscribepro-magento2-ext/releases">'
            . 'View Releases'
            . '</a>]';
    }
}
