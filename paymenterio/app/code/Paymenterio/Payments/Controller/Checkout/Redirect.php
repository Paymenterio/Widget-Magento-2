<?php
namespace Paymenterio\Payments\Controller\Checkout;

use Paymenterio\Payments\Helper\Data;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Redirect extends Action
{
    protected $_helper;

    public function __construct(Context $context, Data $helper)
    {
        parent::__construct($context);
        $this->_helper = $helper;
    }

    public function execute()
    {
        $incrementId = $this->_objectManager->get('Magento\Checkout\Model\Session')->getLastRealOrderId();
        $orderFactory = $this->_objectManager->get('Magento\Sales\Model\OrderFactory');
        $order = $orderFactory->create()->loadByIncrementId($incrementId);

        $payment = $this->_helper->createPayment($order);
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($payment->payment_link);

        return $redirect;
    }

}
