<?php

namespace Swarming\SubscribePro\Helper;

use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Swarming\SubscribePro\Api\Data\ProductInterface as PlatformProductInterface;
use Swarming\SubscribePro\Api\Data\SubscriptionOptionInterface;
use Swarming\SubscribePro\Model\Quote\SubscriptionOption\OptionProcessor;

class QuoteItem
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    protected $itemOptionFactory;

    /**
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
    ) {
        $this->itemOptionFactory = $itemOptionFactory;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function hasSubscription($item)
    {
        return $this->getCreateNewSubscriptionAtCheckout($item) || $this->isItemFulfilsSubscription($item);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function isSubscriptionEnabled($item)
    {
        return $this->getSubscriptionOption($item) == PlatformProductInterface::SO_SUBSCRIPTION;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return null|string
     */
    public function getSubscriptionOption($item)
    {
        return $this->getParam($item, SubscriptionOptionInterface::OPTION);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function isItemFulfilsSubscription($item)
    {
        return (bool) $this->getParam($item, SubscriptionOptionInterface::ITEM_FULFILS_SUBSCRIPTION);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function getCreateNewSubscriptionAtCheckout($item)
    {
        return $this->getParam($item, SubscriptionOptionInterface::CREATE_NEW_SUBSCRIPTION_AT_CHECKOUT);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function getItemAddedBySubscribePro($item)
    {
        return $this->getParam($item, SubscriptionOptionInterface::ITEM_ADDED_BY_SUBSCRIBE_PRO);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return null|string
     */
    public function getSubscriptionInterval($item)
    {
        return $this->getParam($item, SubscriptionOptionInterface::INTERVAL);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return null|string
     */
    public function getNextOrderDate($item)
    {
        return $this->getParam($item, SubscriptionOptionInterface::NEXT_ORDER_DATE);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return null|string
     */
    public function getFixedPrice($item)
    {
        return $this->getParam($item, SubscriptionOptionInterface::FIXED_PRICE);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return null|string
     */
    public function getSubscriptionId($item)
    {
        return $this->getParam($item, SubscriptionOptionInterface::SUBSCRIPTION_ID);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param string $paramKey
     * @return mixed|null
     */
    protected function getParam($item, $paramKey)
    {
        $params = $this->getSubscriptionParams($item);
        return isset($params[$paramKey]) ? $params[$paramKey] : null;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param string $paramKey
     * @param string $paramValue
     * @return $this
     */
    public function setSubscriptionParam($item, $paramKey, $paramValue)
    {
        $params = $this->getSubscriptionParams($item);
        $params[$paramKey] = $paramValue;
        $this->setSubscriptionParams($item, $params);
        $this->markQuoteItemAsModified($item);
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return array
     */
    public function getSubscriptionParams($item)
    {
        $buyRequest = $item->getOptionByCode('info_buyRequest');
        $buyRequest = $buyRequest ? json_decode($buyRequest->getValue(), true) : [];
        return $buyRequest[OptionProcessor::KEY_SUBSCRIPTION_OPTION] ?? [];
    }

    /**
     * @param $item
     * @param $params
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setSubscriptionParams($item, $params)
    {
        $buyRequestOption = !empty($item->getOptionByCode('info_buyRequest'))
            ? $item->getOptionByCode('info_buyRequest')
            : $this->itemOptionFactory->create()->setProduct($item->getProduct())->setCode('info_buyRequest');

        $buyRequest = $buyRequestOption->getValue() ? json_decode($buyRequestOption->getValue(), true) : [];

        $buyRequest[OptionProcessor::KEY_SUBSCRIPTION_OPTION] = $params;

        /** @var Option $buyRequest */
        $buyRequestOption->setValue(json_encode($buyRequest));
        /** @var Item $item */
        $item->addOption($buyRequestOption);
    }

    /**
     * In case when only options are updated. Options are saved only if quote item is changed.
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     */
    protected function markQuoteItemAsModified($item)
    {
        if (!$item->isObjectNew()) {
            /** @var Item $item */
            $item->setUpdatedAt($this->dateTimeFactory->create()->format('Y-m-d H:i:s'));
        }
    }
}
