<?php

namespace Czende\GoPayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\GatewayInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Czende\GoPayPlugin\SetGoPay;
use Payum\Core\Security\TokenInterface;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
final class CaptureAction implements ActionInterface, GatewayAwareInterface {
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($request) {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = $request->getModel();
        ArrayObject::ensureArrayObject($model);

        $order = $request->getFirstModel()->getOrder();
        $model['customer'] = $order->getCustomer();
        $model['locale'] = $this->getFallbackLocaleCode($order->getLocaleCode());

        $goPayAction = $this->getGoPayAction($request->getToken(), $model);

        $this->getGateway()->execute($goPayAction);
    }


    /**
     * {@inheritdoc}
     */
    public function supports($request) {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }


    /**
     * @return GatewayInterface
     */
    public function getGateway() {
        return $this->gateway;
    }


    /**
     * @param TokenInterface $token
     * @param ArrayObject $model
     *
     * @return SetGoPay
     */
    private function getGoPayAction(TokenInterface $token, ArrayObject $model) {
        $gopayAction = new SetGoPay($token);
        $gopayAction->setModel($model);

        return $gopayAction;
    }


    /**
     * Get order fallback locale
     * @param  string $localeCode
     * @return string
     */
    private function getFallbackLocaleCode($localeCode) {
        return explode('_', $localeCode)[0];
    }
}

