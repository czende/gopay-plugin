<?php

namespace Czende\GoPayPlugin\Action;

use Czende\GoPayPlugin\GoPayApiWrapper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

final class StatusAction implements ActionInterface {
    
    /**
     * Execute request.
     */
    public function execute($request) {
        /** @var $request GetStatusInterface */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $state = $model['state'];

        // dump($request);
        // exit();


        if ($state === null || $state === GoPayApiWrapper::CREATED) {
            $request->markNew();

            return;
        }

        if ($state === GoPayApiWrapper::PENDING_API_STATUS) {
            $request->markPending();

            return;
        }

        if ($state === GoPayApiWrapper::CANCELED_API_STATUS) {
            $request->markCanceled();

            return;
        }

        if ($state === GoPayApiWrapper::COMPLETED_API_STATUS) {
            $request->markCaptured();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
