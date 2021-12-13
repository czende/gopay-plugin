<?php

declare(strict_types=1);

namespace Bratiask\GoPayPlugin\Action;

use ArrayObject;
use Bratiask\GoPayPlugin\Api\GoPayApiInterface;
use Bratiask\GoPayPlugin\SetGoPay;
use JetBrains\PhpStorm\Pure;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject as PayumArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\IdentityInterface;
use RuntimeException;
use Sylius\Component\Core\Model\CustomerInterface;
use Webmozart\Assert\Assert;

class GoPayAction implements ApiAwareInterface, ActionInterface
{
    use UpdateOrderActionTrait;

    private array $api = [];

    public function __construct(
        private GoPayApiInterface $gopayApi,
        private Payum             $payum
    )
    {
    }

    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $goId = $this->api['goid'];
        $clientId = $this->api['clientId'];
        $clientSecret = $this->api['clientSecret'];
        $isProductionMode = $this->api['isProductionMode'];

        $model = PayumArrayObject::ensureArrayObject($request->getModel());

        $gopayApi = $this->gopayApi;
        $gopayApi->authorize($goId, $clientId, $clientSecret, $isProductionMode, $model['locale']);

        if (null === $model['orderId'] || null === $model['externalPaymentId']) {
            $token = $request->getToken();
            $order = $this->prepareOrder($token, $model, $goId);
            $response = $gopayApi->create($order);

            if ($response && false === isset($response->json['errors']) && GoPayApiInterface::CREATED === $response->json['state']) {
                $model['orderId'] = $response->json['order_number'];
                $model['externalPaymentId'] = $response->json['id'];
                $request->setModel($model);

                throw new HttpRedirect($response->json['gw_url']);
            }

            throw new RuntimeException('GoPay error: ' . $response->__toString());
        } else {
            $this->updateExistingOrder($this->gopayApi, $request, $model);
        }
    }

    #[Pure]
    public function supports(mixed $request): bool
    {
        return $request instanceof SetGoPay && $request->getModel() instanceof ArrayObject;
    }

    public function setApi(mixed $api): void
    {
        if (!is_array($api)) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    public function setGoPayApi(GoPayApiInterface $gopayApi): void
    {
        $this->gopayApi = $gopayApi;
    }

    private function prepareOrder(TokenInterface $token, PayumArrayObject $model, string $goid): array
    {
        $notifyToken = $this->createNotifyToken($token->getGatewayName(), $token->getDetails());

        $order = [];
        $order['target']['type'] = 'ACCOUNT';
        $order['target']['goid'] = $goid;
        $order['currency'] = $model['currencyCode'];
        $order['amount'] = $model['totalAmount'];
        $order['order_number'] = $model['extOrderId'];
        $order['lang'] = $model['locale'];

        /** @var CustomerInterface $customer */
        $customer = $model['customer'];

        Assert::isInstanceOf(
            $customer,
            CustomerInterface::class,
            sprintf(
                'Make sure the first model is the %s instance.',
                CustomerInterface::class
            )
        );

        $payerContact = [
            'email' => (string)$customer->getEmail(),
            'first_name' => (string)$customer->getFirstName(),
            'last_name' => (string)$customer->getLastName(),
        ];

        $order['payer']['contact'] = $payerContact;
        $order['items'] = $this->resolveProducts($model);

        $order['callback']['return_url'] = $token->getTargetUrl();
        $order['callback']['notification_url'] = $notifyToken->getTargetUrl();

        return $order;
    }

    private function resolveProducts(PayumArrayObject $model): array
    {
        if (false === $model->offsetExists('items') || 0 === count($model['items'])) {
            return [
                [
                    'name' => $model['description'],
                    'amount' => $model['totalAmount']
                ]
            ];
        }

        return [];
    }

    private function createNotifyToken(string $gatewayName, IdentityInterface $model): TokenInterface
    {
        return $this->payum->getTokenFactory()->createNotifyToken($gatewayName, $model);
    }
}
