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
namespace Paymenterio\Payments\Model\Library\Helpers;

class SignatureGenerator {

    /**
     * Create SHA1 Hash from input parametrs
     *
     * @param mixed $data
     * @param string $key
     *
     * @return string
     *
     */

    public static function generateSHA1Signature($orderID, $orderKey): string
    {
        return SHA1 ($orderID . '|' . $orderKey);
    }

    public static function verifySHA1Signature($orderID, $orderKey, $hash): bool
    {
        return (SHA1 ($orderID . '|' . $orderKey) === $hash);
    }

    public static function createStringFromArray($data){

        if(!is_array($data)){
            return $data;
        }

        $pureString = "";

        foreach($data as $element){
            $pureString .= self::createStringFromArray($element);
        }

        return $pureString;
    }

}
