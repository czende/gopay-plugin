<?php

namespace Czende\GoPayPlugin\Api;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
interface GoPayApiInterface {

    const CREATED = 'CREATED';
    const PAID = 'PAID';
    const CANCELED = 'CANCELED';
    const TIMEOUTED = 'TIMEOUTED';

    /**
     * @param $goId
     * @param $clientId
     * @param $clientSecret
     * @param $environment
     */
    public function authorize($goId, $clientId, $clientSecret, $environment);

    /**
     * @param $order
     */
    public function create($order);

    /**
     * @param string $paymentId
     */
    public function retrieve($paymentId);
}