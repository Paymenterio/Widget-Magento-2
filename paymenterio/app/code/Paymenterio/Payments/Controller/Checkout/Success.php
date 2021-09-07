<?php
namespace Paymenterio\Payments\Controller\Checkout;

use Magento\Framework\App\Action\Action;

class Success extends Action
{
    public function execute()
    {
        $this->messageManager->addSuccessMessage(__('Płatność została przekazana, dziękujemy. Twoje zamówienie oczekuje na potwierdzenie płatności.'));
        $this->_redirect('checkout/onepage/success/');
    }
}
