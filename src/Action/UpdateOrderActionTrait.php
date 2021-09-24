<?php

declare(strict_types=1);

namespace Bratiask\GoPayPlugin\Action;

use Bratiask\GoPayPlugin\Api\GoPayApiInterface;
use Payum\Core\Bridge\Spl\ArrayObject;

trait UpdateOrderActionTrait
{
    private function updateExistingOrder(GoPayApiInterface $gopayApi, mixed $request, ArrayObject $model): void
    {
        $response = $gopayApi->retrieve($model['externalPaymentId']);

        if (GoPayApiInterface::PAID === $response->json['state']) {
            $model['gopayStatus'] = $response->json['state'];
            $request->setModel($model);
        }

        if (GoPayApiInterface::CANCELED === $response->json['state']) {
            $model['gopayStatus'] = $response->json['state'];
            $request->setModel($model);
        }

        if (GoPayApiInterface::TIMEOUTED === $response->json['state']) {
            $model['gopayStatus'] = $response->json['state'];
            $request->setModel($model);
        }

        if (GoPayApiInterface::CREATED === $response->json['state']) {
            $model['gopayStatus'] = GoPayApiInterface::CANCELED;
            $request->setModel($model);
        }
    }
}
