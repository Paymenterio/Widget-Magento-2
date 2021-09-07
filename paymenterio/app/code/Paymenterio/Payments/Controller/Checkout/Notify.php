<?php
namespace Paymenterio\Payments\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use \Paymenterio\Payments\Helper\Notify as NotifyHelper;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;


class Notify extends Action implements CsrfAwareActionInterface
{
    private $requestParams;
    protected $_notifyHelper;

    public function __construct(Context $context, NotifyHelper $notifyHelper)
    {
        parent::__construct($context);
        $this->_notifyHelper = $notifyHelper;
        $this->requestParams = $this->getRequest()->getParams();
    }

    public function createCsrfValidationException(RequestInterface $request): ? InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $result = $this->_notifyHelper->process($this->requestParams);
        switch ($result) {
            case "OrderNotFoundException":
                $this->getResponse()
                    ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_404)
                    ->setContent('Order not Found or was Completed successfully.');
                return;
            case "WrongSignatureException":
                $this->getResponse()
                    ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_403)
                    ->setContent('Signature mismatch');
                return;
            case "SUCCESS":
                $this->getResponse()
                    ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_200)
                    ->setContent('OK');
                return;
            default:
                $this->getResponse()
                    ->setStatusCode(\Magento\Framework\App\Response\Http::STATUS_CODE_400)
                    ->setContent('Wrong request!');
                return;
        }
    }
}
