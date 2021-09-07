<?php
namespace Paymenterio\Payments\Controller\Checkout;

use Magento\Framework\App\Action\Action;

class Failure extends Action
{
    public function execute()
    {
        $this->messageManager
            ->addErrorMessage(__('Płatność nie została zaksięgowana.'));
        $this->_redirect('checkout/onepage/failure/');
    }
}