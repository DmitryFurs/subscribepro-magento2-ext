<?php

namespace Swarming\SubscribePro\Block\Adminhtml\System\Config;

use SubscribePro\Exception\InvalidArgumentException;
use Swarming\SubscribePro\Gateway\Config\Config as SubscribeProConfig;

class Config extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Swarming\SubscribePro\Gateway\Config\ConfigProvider
     */
    protected $gatewayConfigProvider;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $quoteSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Swarming\SubscribePro\Gateway\Config\Config
     */
    private $sProConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Swarming\SubscribePro\Gateway\Config\ConfigProvider $gatewayConfigProvider
     * @param \Swarming\SubscribePro\Gateway\Config\Config $sProConfig
     * @param \Magento\Backend\Model\Session\Quote $quoteSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Swarming\SubscribePro\Gateway\Config\ConfigProvider $gatewayConfigProvider,
        SubscribeProConfig $sProConfig,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->gatewayConfigProvider = $gatewayConfigProvider;
        $this->quoteSession = $quoteSession;
        $this->logger = $logger;
        $this->sProConfig = $sProConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getPaymentConfig()
    {
        $config = [];
        $stores = $this->_storeManager->getStores();

        foreach ($stores as $store) {
            $storeId = $store->getId();
            if (!$this->sProConfig->isActive($storeId)) {
                continue;
            }
            try {
                $config[$storeId] = $this->gatewayConfigProvider->getConfig($storeId);
            } catch (InvalidArgumentException $e) {
                $config = null;
                $this->logger->debug('Cannot retrieve Subscribe Pro payment config: ' . $e->getMessage());
            }
        }

        return json_encode($config);
    }
}
