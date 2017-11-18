<?php

namespace Czende\GoPayPlugin\Action;

use Czende\GoPayPlugin\GoPayWrapper;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
final class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface {
    use GatewayAwareTrait;

    /**
     * Execute convert payment action and prepare body for request
     * @param mixed $request
     * @throws Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request) {
        RequestNotSupportedException::assertSupports($this, $request);

        /**
         * @var Payum\Core\Model\PaymentInterface
         */
        $payment = $request->getSource();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['totalAmount'] = $payment->getTotalAmount();
        $details['currencyCode'] = $payment->getCurrencyCode();
        $details['extOrderId'] = uniqid($payment->getNumber());
        $details['description'] = $payment->getDescription();
        $details['client_email'] = $payment->getClientEmail();
        $details['client_id'] = $payment->getClientId();
        $details['customerIp'] = $this->getClientIp();
        $details['status']  = GoPayWrapper::CREATED;

        $request->setResult((array) $details);
    }


    /**
     * @param mixed $request
     * @return boolean
     */
    public function supports($request) {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array';
    }
    

    /**
     * @return string|null
     */
    private function getClientIp() {
        return array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null;
    }
}