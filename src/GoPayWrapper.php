<?php

namespace Czende\GoPayPlugin;

use GoPay\Api;

/**
 * Wrapper for gopay SDK.
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
final class GoPayWrapper implements GoPayWrapperInterface {
    
    // Payment statuses temporary WITHOUT "refunded"
    const CREATED = 'CREATED';
    const PAID = 'PAID';
    const CANCELED = 'CANCELED';
    const TIMEOUTED = 'TIMEOUTED';

    /**
     * @var Gopay\Api
     */
    var $gopay;


    /**
     * Set GoPay config
     * @param string $goid        
     * @param string $clientId    
     * @param string $clientSecret
     * @param string $environment 
     */
    public function __construct($goid, $clientId, $clientSecret, $environment) {
        $this->gopay = Api::payments([
            'goid' => $goid,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'isProductionMode' => $environment == 'production' ? true : false
        ]);
    }


    /**
     * Create payment based on given order
     * @return \GoPay\Http\Response
     */
    public function create($order) {
        return $this->gopay->createPayment($order);
    }


    /**
     * Retrieve payment based on unique payment ID
     * @return \GoPay\Http\Response
     */
    public function retrieve($paymentID) {
        return $this->gopay->getStatus($paymentID);
    }
}