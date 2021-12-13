<?php

declare(strict_types=1);

namespace Bratiask\GoPayPlugin\Action;

use ArrayAccess;
use Bratiask\GoPayPlugin\SetGoPay;
use JetBrains\PhpStorm\Pure;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = $request->getModel();
        ArrayObject::ensureArrayObject($model);

        /** @var OrderInterface $order */
        $order = $request->getFirstModel()->getOrder();
        $model['customer'] = $order->getCustomer();
        $model['locale'] = $this->fallbackLocaleCode($order->getLocaleCode());

        $this->gateway->execute($this->goPayAction($request->getToken(), $model));
    }

    #[Pure]
    public function supports(mixed $request): bool
    {
        return $request instanceof Capture && $request->getModel() instanceof ArrayAccess;
    }

    private function goPayAction(TokenInterface $token, ArrayObject $model): SetGoPay
    {
        $gopayAction = new SetGoPay($token);
        $gopayAction->setModel($model);

        return $gopayAction;
    }

    private function fallbackLocaleCode(string $localeCode): string
    {
        return explode('_', $localeCode)[0];
    }
}
