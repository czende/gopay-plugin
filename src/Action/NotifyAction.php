<?php

namespace Czende\GoPayPlugin\Action;

use Czende\GoPayPlugin\SetGoPay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
final class NotifyAction implements ActionInterface, GatewayAwareInterface {
    use GatewayAwareTrait;

    /**
     * @param mixed $request
     * @throws Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request) {
        /** @var $request Payum\Core\Request\Notify */
        RequestNotSupportedException::assertSupports($this, $request);

        $setGoPay = new SetGoPay($request->getToken());
        $setGoPay->setModel($request->getModel());;
        $this->getGateway()->execute($setGoPay);

        $status = new GetHumanStatus($request->getToken());
        $status->setModel($request->getModel());
        $this->getGateway()->execute($status);
    }


    /**
     * @return Payum\Core\GatewayInterface
     */
    public function getGateway() {
        return $this->gateway;
    }


    /**
     * @param mixed $request
     * @return boolean
     */
    public function supports($request) {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayObject;
    }
}
