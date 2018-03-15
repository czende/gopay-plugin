<?php

declare(strict_types=1);

namespace Czende\GoPayPlugin\Api;

use GoPay\Api;
use GoPay\Definition\Language;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
final class GoPayApi implements GoPayApiInterface {

    /** @var Api */
    var $gopay;

    /**
     * @param string $goid        
     * @param string $clientId    
     * @param string $clientSecret
     * @param string $environment 
     */
    public function authorize($goId, $clientId, $clientSecret, $environment) {
        $this->gopay = Api::payments([
            'goid' => $goId,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'isProductionMode' => $environment,
            'language' => Language::CZECH
        ]);
    }


    /**
     * @param $order
     */
    public function create($order) {
        return $this->gopay->createPayment($order);
    }


    /**
     * @param string $paymentId
     */
    public function retrieve($paymentId) {
        return $this->gopay->getStatus($paymentId);
    }
}