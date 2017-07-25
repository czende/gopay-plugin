<?php

namespace Czende\GoPayPlugin;

use GoPay;

/**
 * Wrapper for gopay SDK
 */
final class GoPayApiWrapper {
	// Stats consts
    const NEW_API_STATUS = 'CREATED';
    const PENDING_API_STATUS = 'PAYMENT_METHOD_CHOSEN';
    const COMPLETED_API_STATUS = 'PAID';
    const SUCCESS_API_STATUS = 'FINISHED';
    const CANCELED_API_STATUS = 'CANCELED';
    const TIMEOUTED_API_STATUS = 'TIMEOUTED';
    const REFUNDED_API_STATUS = 'REFUNDED';
    const PARTIALLY_REFUNDED_API_STATUS = 'PARTIALLY_REFUNDED';

    /* @var Gopay\Api */
    var $gopay;

    public function __construct($goid, $clientId, $clientSecret, $isProductionMode) {
        $this->gopay = GoPay\Api::payments([
			'goid' => $goid,
			'clientId' => $clientId,
			'clientSecret' => $clientSecret,
			'isProductionMode' => $isProductionMode
		]);
    }

    /**
     * Create payment from order
     */
    public function create($order) {
    	return $this->gopay->createPayment($order);
    }

    /**
     * Retrieve orderId from GoPay API
     */
    public function retrieve($orderId) {
    	return $this->create($orderId);
    }
}
