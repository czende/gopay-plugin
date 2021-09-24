<?php

declare(strict_types=1);

namespace Bratiask\GoPayPlugin\Action;

use Bratiask\GoPayPlugin\Api\GoPayApiInterface;
use JetBrains\PhpStorm\Pure;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

final class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['totalAmount'] = $payment->getTotalAmount();
        $details['currencyCode'] = $payment->getCurrencyCode();
        $details['extOrderId'] = uniqid($payment->getNumber());
        $details['description'] = $payment->getDescription();
        $details['client_email'] = $payment->getClientEmail();
        $details['client_id'] = $payment->getClientId();
        $details['customerIp'] = $this->customerIp();
        $details['status'] = GoPayApiInterface::CREATED;

        $request->setResult((array)$details);
    }

    #[Pure]
    public function supports(mixed $request): bool
    {
        return $request instanceof Convert && $request->getSource() instanceof PaymentInterface && 'array' === $request->getTo();
    }

    private function customerIp(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }
}
