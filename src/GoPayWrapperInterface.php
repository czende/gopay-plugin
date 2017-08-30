<?php

namespace Czende\GoPayPlugin;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
interface GoPayWrapperInterface {

    /**
     * Create request for GoPay API
     * @param $config
     * @return mixed
     */
    public function create($config);

    /**
     * Get status of the payment based on given GoPay payment ID
     * @param $paymentID
     * @return mixed
     */
    public function retrieve($paymentID);
}