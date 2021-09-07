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
namespace Paymenterio\Payments\Model\Library\Model;

use Paymenterio\Payments\Model\Library\Interfaces\Data;
use Paymenterio\Payments\Model\Library\Services\PaymenterioTransactionException;

class Amount implements Data{

	/**
	 *
	 * @var array $paymentChannels
	 * @var string $lang
	 */
	private $value, $currencyCode;

	/**
	 *
	 * @param float $value
	 * @param string $currencyCode
	 */
	function __construct($value, $currencyCode) {

		if (! is_numeric ( $value )) {
			throw new PaymenterioTransactionException ( "Amount value not numeric" );
		}

		if (strlen ( $currencyCode ) != 3) {
			throw new PaymenterioTransactionException ( "Currency code not valid" );
		}

		$this->value = $value;
		$this->currencyCode = $currencyCode;
	}
	public static function fromArray($array) {
		return new Amount($array['value'], $array['currencyCode']);
	}

	/**
	 *
	 * @see PaymenterioData::toArray()
	 */
	public function toArray() {
		$array = array ();
		foreach ($this as $key => $value ) {
			$array ["amount.".$key ] = $value;
		}
		return $array;
	}

}
