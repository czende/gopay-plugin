<?php

namespace Czende\GoPayPlugin;

/**
 * Wrapper for gopay SDK
 */
final class GoPayApiWrapper {
	// Stats consts
    const NEW_API_STATUS = 'NEW';
    const PENDING_API_STATUS = 'PENDING';
    const COMPLETED_API_STATUS = 'COMPLETED';
    const SUCCESS_API_STATUS = 'SUCCESS';
    const CANCELED_API_STATUS = 'CANCELED';

    /* @var \GoPay\Api */
    var $gopay;

    public function __construct($goid, $clientId, $clientSecret, $isProductionMode) {
    	$this->gopay = \GoPay\Api::payments([
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
