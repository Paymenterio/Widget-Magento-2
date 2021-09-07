<?php
namespace Paymenterio\Payments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order;
use Paymenterio\Payments\Model\Library\Shop;
use Paymenterio\Payments\Model\Library\Services\PaymenterioException;
use Paymenterio\Payments\Model\Library\Helpers\SignatureGenerator;
/**
 * Class Data.
 * @package Paymenterio\Payments\Helper
 */
final class Data extends AbstractHelper
{
    public function __construct(\Magento\Framework\App\Helper\Context $context)
    {
        parent::__construct($context);
    }

    public function createPayment(Order $order)
    {
        $shop = new Shop($this->getConfigData('paymenterio_shop_id'), $this->getConfigData('paymenterio_api_key'));

        $urls = $this->getReturnUrlsForOrder($order);

        try {
            $payment = $shop->createPayment(
                1,
                $order->getId(),
                $this->getAmountForOrder($order),
                $this->getNameForOrder($order),
                $urls['successUrl'],
                $urls['failUrl'],
                $urls['notifyUrl']
            );
        } catch (PaymenterioException $e) {
            exit ($e);
        }

        return $payment;
    }

    public function getReturnUrlsForOrder($order)
    {
        return array(
            'successUrl' =>  $this->_urlBuilder->getUrl('paymenterio/checkout/success'),
            'failUrl' => $this->_urlBuilder->getUrl('paymenterio/checkout/failure'),
            'notifyUrl' => $this->buildNotifyUrl($order)
        );
    }

    private function buildNotifyUrl(Order $order) {
        $url = $this->_urlBuilder->getUrl('paymenterio/checkout/notify');
        return $url .= '?hash=' . SignatureGenerator::generateSHA1Signature($order->getId(), $order->getIncrementId());
    }

    public function getAmountForOrder($order)
    {
        return array(
            "value"=> $this->roundToTwoDecimal($order->getGrandTotal()),
            "currencyCode"=>$order->getOrderCurrencyCode()
        );
    }

    public function getNameForOrder($order) {
        return "Płatność za zamówienie {$order->getId()}";
    }

    public function getConfigData($field)
    {
        return $this->scopeConfig->getValue('payment/paymenterio_payments/' . $field,
            ScopeInterface::SCOPE_STORE);
    }

    public function roundToTwoDecimal($price)
    {
        return number_format((float) $price, 2, '.', '');
    }

}
