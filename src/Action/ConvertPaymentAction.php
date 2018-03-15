<?php

namespace Czende\GoPayPlugin\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Czende\GoPayPlugin\Api\GoPayApiInterface;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
final class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface {
    use GatewayAwareTrait;

    /**
     * @param mixed $request
     * 
     * @throws RequestNotSupportedException
     */
    public function execute($request) {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface */
        $payment = $request->getSource();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['totalAmount'] = $payment->getTotalAmount();
        $details['currencyCode'] = $payment->getCurrencyCode();
        $details['extOrderId'] = uniqid($payment->getNumber());
        $details['description'] = $payment->getDescription();
        $details['client_email'] = $payment->getClientEmail();
        $details['client_id'] = $payment->getClientId();
        $details['customerIp'] = $this->getClientIp();
        $details['status']  = GoPayApiInterface::CREATED;

        $request->setResult((array) $details);
    }


    /**
     * @param mixed $request
     * 
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