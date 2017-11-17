<?php

namespace Czende\GoPayPlugin\Action;

use Czende\GoPayPlugin\GoPayWrapper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;


/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
final class StatusAction implements ActionInterface {
    
    /**
     * Execute request based on status.
     * @param mixed $request
     * @throws Payum\Core\Exception\RequestNotSupportedException if the action doesn't support the request.
     */
    public function execute($request) {
        /** @var $request GetStatusInterface */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $status = $model['status'];

        if ($status === null || $status === GoPayWrapper::CREATED) {
            if (isset($model['external_payment_id'])) {
                $request->markCanceled();
            } else {
                $request->markNew();
            }

            return;
        }

        if ($status === GoPayWrapper::PAID) {
            $request->markCaptured();

            return;
        }

        if ($status === GoPayWrapper::CANCELED) {
            $request->markCanceled();

            return;
        }

        $request->markUnknown();
    }

    /**
     * @param mixed $request
     * @return boolean
     */
    public function supports($request) {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
