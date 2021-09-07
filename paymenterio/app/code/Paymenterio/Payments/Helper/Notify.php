<?php
namespace Paymenterio\Payments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order;
use Paymenterio\Payments\Model\Library\Helpers\SignatureGenerator;

final class Notify extends AbstractHelper
{
    const ABORT = 'Abort';

    public function process(array $data)
    {
        $hash = $data['hash'];
        $body = json_decode(file_get_contents("php://input"), true);
        $statusID = 0;
        $orderID = 0;
        $order = null;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderFactory = $objectManager->get('Magento\Sales\Model\OrderFactory');

        if (isset($body['order']) && !empty($body['order'])) {
            $orderID = $body['order'];
            $order = $orderFactory->create()->loadByAttribute('entity_id', $orderID);
        }

        if (isset($body['status']) && !empty($body['status'])) {
            $statusID = $body['status'];
        }

        if (empty($order)) {
            return "OrderNotFoundException";
        }

        $isSignatureValid = SignatureGenerator::verifySHA1Signature($orderID, $order->getIncrementId(), $hash);
        if (!$isSignatureValid) {
            return "WrongSignatureException";
        }

        if ($order->getStatus() != Order::STATE_COMPLETE && $order->getStatus() != Order::STATE_PROCESSING) {
            if ($statusID == 5) {
                $this->complete($order, $body['transaction_hash']);
            } elseif ($statusID <= 4) {
                $this->addNotify($order, $statusID);
            } else {
                $this->reject($order, $statusID);
            }
            return "SUCCESS";
        }

        return "NoActionException";
    }

    private function complete($order, $transactionHash)
    {
        $payment = $order->getPayment();
        $payment->setTransactionId($transactionHash);
        $payment
            ->setIsTransactionApproved(true)
            ->setShouldCloseParentTransaction(true)
            ->setIsTransactionClosed(true);

        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();
            $invoice->register();
            $invoice->setTransactionId($transactionHash);
            $order->addRelatedObject($invoice);
        }
        $order->addStatusHistoryComment(__('Płatność została zaksięgowana w systemie Paymenterio.'), Order::STATE_COMPLETE);
        $order->getResource()->save($order);
    }

    private function reject($order, $statusID)
    {
        $order->cancel();
        $order->addStatusHistoryComment(__("Płatność została anulowana przez klienta lub wystąpił błąd podczas jej przetwarzania. Status końcowy transakcji to $statusID."));
        $order->getResource()->save($order);
    }

    private function addNotify($order, $statusID) {
        $order->addStatusHistoryComment(__("Status płatności uległ zmianie. Obecny status to $statusID."));
        $order->getResource()->save($order);
    }

    public function getConfigData($field)
    {
        return $this->scopeConfig->getValue('payment/paymenterio_payments/' . $field,
            ScopeInterface::SCOPE_STORE);
    }
}
