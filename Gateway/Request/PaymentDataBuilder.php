<?php

namespace Swarming\SubscribePro\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use SubscribePro\Service\PaymentProfile\PaymentProfileInterface;

class PaymentDataBuilder implements BuilderInterface
{
    public const PAYMENT_METHOD_TOKEN = 'payment_method_token';

    /**
     * @var \Swarming\SubscribePro\Gateway\Helper\SubjectReader
     */
    protected $subjectReader;

    /**
     * @param \Swarming\SubscribePro\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \Swarming\SubscribePro\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return string[]
     * @throws \InvalidArgumentException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        return [
            self::PAYMENT_METHOD_TOKEN => $payment->getAdditionalInformation(self::PAYMENT_METHOD_TOKEN),
            VaultConfigProvider::IS_ACTIVE_CODE => $payment->getAdditionalInformation(
                VaultConfigProvider::IS_ACTIVE_CODE
            ),
            PaymentProfileInterface::CREDITCARD_TYPE => $payment->getCcType(),
            PaymentProfileInterface::CREDITCARD_MONTH => $payment->getCcExpMonth(),
            PaymentProfileInterface::CREDITCARD_YEAR => $payment->getCcExpYear(),
            PaymentProfileInterface::CREDITCARD_LAST_DIGITS => $payment->getAdditionalInformation(
                PaymentProfileInterface::CREDITCARD_LAST_DIGITS
            ),
            PaymentProfileInterface::CREDITCARD_FIRST_DIGITS => $payment->getAdditionalInformation(
                PaymentProfileInterface::CREDITCARD_FIRST_DIGITS
            )
        ];
    }
}
