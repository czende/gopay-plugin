<?php

namespace Czende\GoPayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Czende\GoPayPlugin\Api\GoPayApiInterface;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
class StatusAction implements ActionInterface {
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request) {
        /** @var $request GetStatusInterface */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $status = isset($model['gopayStatus']) ? $model['gopayStatus'] : null;

        // Maybe $model['externalPaymentId']

        if ((null === $status || GoPayApiInterface::CREATED === $status) && false === isset($model['orderId'])) {
            $request->markNew();
            return;
        }

        if (GoPayApiInterface::CANCELED === $status) {
            $request->markCanceled();
            return;
        }

        if (GoPayApiInterface::TIMEOUTED === $status) {
            $request->markCanceled();
            return;
        }

        if (GoPayApiInterface::PAID === $status) {
            $request->markCaptured();
            return;
        }

        $request->markUnknown();
    }

    
    /**
     * {@inheritDoc}
     */
    public function supports($request) {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
