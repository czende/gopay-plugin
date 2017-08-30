<?php

namespace Czende\GoPayPlugin\Action;

use Czende\GoPayPlugin\SetGoPay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
final class CaptureAction implements ActionInterface, GatewayAwareInterface {
    use GatewayAwareTrait;

    /**
     * Execute capture action based on given request and prepare customer and order.
     * @param mixed $request
     * @throws Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request) {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = $request->getModel();
        ArrayObject::ensureArrayObject($model);

        $model['customer'] = $request->getFirstModel()->getOrder()->getCustomer();
        $model['order'] = $request->getFirstModel()->getOrder();

        $goPayAction = $this->getGoPayAction($request->getToken(), $model);

        $this->getGateway()->execute($goPayAction);
    }

    /**
     * @param mixed $request
     * @return boolean
     */
    public function supports($request) {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }


    /**
     * @return Payum\Core\GatewayInterface
     */
    public function getGateway() {
        return $this->gateway;
    }


    /**
     * @param TokenInterface    $token
     * @param ArrayObject       $model
     * @return mixed
     */
    private function getGoPayAction(TokenInterface $token, ArrayObject $model) {
        $goPayAction = new SetGoPay($token);
        $goPayAction->setModel($model);

        return $goPayAction;
    }
}