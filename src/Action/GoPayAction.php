<?php
declare(strict_types=1);

namespace Bratiask\GoPayPlugin\Action;

use ArrayObject;
use Bratiask\GoPayPlugin\Api\GoPayApiInterface;
use Bratiask\GoPayPlugin\SetGoPay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject as CoreArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Payum;
use Payum\Core\Storage\IdentityInterface;
use RuntimeException;
use Sylius\Component\Core\Model\CustomerInterface;
use GoPay\Definition\Language;
use Webmozart\Assert\Assert;

class GoPayAction implements ApiAwareInterface, ActionInterface
{
    protected $gopayApi;
    private $api = [];
    private $payum;

    public function __construct(GoPayApiInterface $gopayApi, Payum $payum)
    {
        $this->gopayApi = $gopayApi;
        $this->payum = $payum;
    }

    /**
     * @param Generic $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $goId = $this->api['goid'];
        $clientId = $this->api['clientId'];
        $clientSecret = $this->api['clientSecret'];
        $isProductionMode = $this->api['isProductionMode'];

        $gopayApi = $this->gopayApi;
        $gopayApi->authorize($goId, $clientId, $clientSecret, $isProductionMode);

        $model = CoreArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['orderId'] || null === $model['externalPaymentId']) {
            // New order.
            $token = $request->getToken();
            $order = $this->prepareOrder($token, $model, $goId);
            $response = $gopayApi->create($order);

            if ($response && GoPayApiInterface::CREATED === $response->json['state']) {
                $model['orderId'] = $response->json['order_number'];
                $model['externalPaymentId'] = $response->json['id'];
                $request->setModel($model);

                throw new HttpRedirect($response->json['gw_url']);
            }

            throw new RuntimeException($response->__toString());
        } else {
            // Existing order.
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

    public function supports($request): bool
    {
        return $request instanceof SetGoPay && $request->getModel() instanceof ArrayObject;
    }

    public function setApi($api): void
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

    private function prepareOrder(TokenInterface $token, CoreArrayObject $model, string $goid): array
    {
        $notifyToken = $this->createNotifyToken($token->getGatewayName(), $token->getDetails());

        $order = [];
        $order['target']['type'] = 'ACCOUNT';
        $order['target']['goid'] = $goid;
        $order['currency'] = $model['currencyCode'];
        $order['amount'] = $model['totalAmount'];
        $order['order_number'] = $model['extOrderId'];
        $order['lang'] = Language::ENGLISH;

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

    private function resolveProducts(CoreArrayObject $model): array
    {
        if (!array_key_exists('items', $model) || count($model['items']) === 0) {
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
