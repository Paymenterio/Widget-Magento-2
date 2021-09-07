<?php
/**
 *
 * Paymenterio Payment PHP SDK
 *
 * @author Paymenterio
 * @version 1.0.0
 * @license MIT
 * @copyright Paymenterio Sp. z o.o.
 *
 * http://paymenterio.com
 *
 */
namespace Paymenterio\Payments\Model\Library;

use Paymenterio\Payments\Model\Library\Services\CurlConnection;
use Paymenterio\Payments\Model\Library\Services\PaymenterioException;
use Paymenterio\Payments\Model\Library\Services\PaymenterioConfigurationException;
use Paymenterio\Payments\Model\Library\Services\PaymenterioCurlException;
use Paymenterio\Payments\Model\Library\Model\Transaction;
use Paymenterio\Payments\Model\Library\Model\Amount;
/**
 * Class Shop
 *
 * @package Paymenterio\Payments\Model\Library
 */
class Shop
{
    const productionEndpoint = 'https://api.paymenterio.pl/v1/';
    /**
     *
     * @var string $shopID
     * @var string $apiKey
     * @var CurlConnection $curlConnection
     * @var Transaction $transaction
     */
    private $shopID;
    private $apiKey;
    private $curlConnection;

    /**
     *
     * @param string $pointId
     * @param string $pointKey
     * @param boolean $production
     * @throws PaymenterioConfigurationException
     */
    public function __construct($shopID, $apiKey)
    {
        if (empty($shopID) || empty($apiKey)) {
            throw new PaymenterioConfigurationException("Configuration required params not set");
        }

        if (strlen($apiKey) < 30 && strlen($apiKey) > 50) {
            throw new PaymenterioConfigurationException("Payment API Key invalid value");
        }

        $this->shopID = $shopID;
        $this->apiKey = $apiKey;
        $this->curlConnection = new CurlConnection(self::productionEndpoint, $apiKey);
    }

    /**
     *
     * @param int $system
     * @param string $orderID
     * @param PaymenterioAmount | array $amount
     * @param string $name
     * @throws PaymenterioException
     * @return mixed
     */
    public function createPayment(int $system, string $orderID, $amount, string $name, string $successUrl, string $failUrl, string $notifyUrl, $fake = false)
    {
        try {

            if (! ($amount instanceof Amount)) {
                $amount = Amount::fromArray($amount);
            }

            $transactionData = new Transaction($system, $this->shopID, $orderID, $amount, $name, $successUrl, $failUrl, $notifyUrl);

            if ($fake) {
                $paymentData = array(
                    'status' => 5,
                    'order' => $orderID
                );
                return json_decode(json_encode($paymentData));
            }
            return $this->curlConnection->post("pay", $transactionData);
        } catch (PaymenterioException $exception) {
            throw new PaymenterioCurlException("Create Payment Exception " . $exception->getMessage());
        }
    }
}

