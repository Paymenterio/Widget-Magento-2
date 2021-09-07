<?php
namespace Paymenterio\Payments\Model;

use Paymenterio\Payments\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\ScopeInterface;

class PaymenterioPayments implements MethodInterface
{
    protected $_code = 'paymenterio',
     $_formBlockType = 'Magento\Payment\Block\Form',
     $_infoBlockType = 'Magento\Payment\Block\Info',
     $_isGateway = false,
     $_isOffline = false,
     $_canOrder = false,
     $_canAuthorize = false,
     $_canCapture = false,
     $_canCapturePartial = false,
     $_canCaptureOnce = false,
     $_canRefund = false,
     $_canRefundInvoicePartial = false,
     $_canVoid = false,
     $_canUseInternal = true,
     $_canUseCheckout = true,
     $_isInitializeNeeded = false,
     $_canFetchTransactionInfo = false,
     $_canReviewPayment = false,
     $_canCancelInvoice = true,
     $_paymentData,
     $_scopeConfig,
     $_storeId,
     $_infoInstance,
     $_eventManager,
     $_helper,
     $_helperRefund;

    public function __construct(
        Data $helperData,
        \Magento\Framework\Model\Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_helper       = $helperData;
        $this->_eventManager = $context->getEventDispatcher();
        $this->_scopeConfig  = $scopeConfig;
    }

    protected function initializeData($data = [])
    {
        if (!empty($data['formBlockType'])) {
            $this->_formBlockType = $data['formBlockType'];
        }
    }

    public function setStore($storeId)
    {
        $this->_storeId = $storeId;
    }

    public function getStore()
    {
        return $this->_storeId;
    }

    public function canOrder()
    {
        return $this->_canOrder;
    }

    public function canAuthorize()
    {
        return $this->_canAuthorize;
    }

    public function canCapture()
    {
        return $this->_canCapture;
    }

    public function canCapturePartial()
    {
        return $this->_canCapturePartial;
    }

    public function canCaptureOnce()
    {
        return $this->_canCaptureOnce;
    }

    public function canRefund()
    {
        return $this->_canRefund;
    }

    public function canRefundPartialPerInvoice()
    {
        return $this->_canRefundInvoicePartial;
    }

    public function canVoid()
    {
        return $this->_canVoid;
    }

    public function canUseInternal()
    {
        return $this->_canUseInternal;
    }

    public function canUseCheckout()
    {
        return $this->_canUseCheckout;
    }

    public function canEdit()
    {
        return true;
    }

    public function canFetchTransactionInfo()
    {
        return $this->_canFetchTransactionInfo;
    }

    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return [];
    }

    public function isGateway()
    {
        return $this->_isGateway;
    }

    public function isOffline()
    {
        return $this->_isOffline;
    }

    public function isInitializeNeeded()
    {
        return $this->_isInitializeNeeded;
    }

    public function canUseForCountry($country)
    {
        return true;
    }

    public function canUseForCurrency($currencyCode)
    {
        return true;
    }

    public function getCode()
    {
        if (empty($this->_code)) {
            throw new LocalizedException(__('We cannot retrieve the payment method code.'));
        }

        return $this->_code;
    }

    public function getFormBlockType()
    {
        return $this->_formBlockType;
    }

    public function getInfoBlockType()
    {
        return $this->_infoBlockType;
    }

    public function getInfoInstance()
    {
        $instance = $this->_infoInstance;
        if (!$instance instanceof InfoInterface) {
            throw new LocalizedException(__('We cannot retrieve the payment information object instance.'));
        }

        return $instance;
    }

    public function setInfoInstance(InfoInterface $info)
    {
        $this->_infoInstance = $info;
    }

    public function refund(InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new LocalizedException(__('The refund action is not available.'));
        }

        if (!$this->_helperRefund->verifyResponse($this->_helperRefund
            ->proceed($payment->getData()['creditmemo']->getOrder(), $amount))) {
            throw new \Paymenterio\Payments\Exception\Error('Online refund fails');
        }

        return $this;
    }

    public function validate()
    {
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Payment) {
            $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        }
        if (!$this->canUseForCountry($billingCountry)) {
            throw new LocalizedException(
                __('You can\'t use the payment type you selected to make payments to the billing country.')
            );
        }

        return $this;
    }

    public function order(InfoInterface $payment, $amount)
    {
        if (!$this->canOrder()) {
            throw new LocalizedException(__('The order action is not available.'));
        }

        return $this;
    }

    public function authorize(InfoInterface $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            throw new LocalizedException(__('The authorize action is not available.'));
        }

        return $this;
    }

    public function capture(InfoInterface $payment, $amount)
    {
        if (!$this->canCapture()) {
            throw new LocalizedException(__('The capture action is not available.'));
        }

        return $this;
    }

    public function cancel(InfoInterface $payment)
    {
        return $this;
    }

    public function void(InfoInterface $payment)
    {
        if (!$this->canVoid()) {
            throw new LocalizedException(__('The void action is not available.'));
        }

        return $this;
    }

    public function canReviewPayment()
    {
        return $this->_canReviewPayment;
    }

    public function acceptPayment(InfoInterface $payment)
    {
        if (!$this->canReviewPayment()) {
            throw new LocalizedException(__('The payment review action is unavailable.'));
        }

        return false;
    }

    public function denyPayment(InfoInterface $payment)
    {
        if (!$this->canReviewPayment()) {
            throw new LocalizedException(__('The payment review action is unavailable.'));
        }

        return false;
    }


    public function getTitle()
    {
        return 'Paymenterio';
    }


    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/paymenterio_payments/' . $field;

        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->_eventManager->dispatch(
            'payment_method_assign_data_' . $this->getCode(),
            [
                AbstractDataAssignObserver::METHOD_CODE => $this,
                AbstractDataAssignObserver::MODEL_CODE  => $this->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE   => $data,
            ]
        );

        $this->_eventManager->dispatch(
            'payment_method_assign_data',
            [
                AbstractDataAssignObserver::METHOD_CODE => $this,
                AbstractDataAssignObserver::MODEL_CODE  => $this->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE   => $data,
            ]
        );

        return $this;
    }

    public function isAvailable(CartInterface $quote = null)
    {
        return (bool) (int) $this->getConfigData('active');
    }

    public function isActive($storeId = null)
    {
        return (bool) (int) $this->getConfigData('active', $storeId);
    }

    public function initialize($paymentAction, $stateObject)
    {
        return $this;
    }

    public function getConfigPaymentAction()
    {
        return $this->getConfigData('payment_action');
    }
}
