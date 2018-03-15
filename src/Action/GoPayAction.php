<?php

namespace Czende\GoPayPlugin\Action;

use Czende\GoPayPlugin\Api\GoPayApiInterface;
use Czende\GoPayPlugin\SetGoPay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Payum;
use Sylius\Component\Core\Model\CustomerInterface;
use GoPay\Definition\Language;
use Webmozart\Assert\Assert;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
class GoPayAction implements ApiAwareInterface, ActionInterface {
    
    /**
     * @var array
     */
    private $api = [];

    /**
     * @var Payum
     */
    private $payum;

    /**
     * @var GoPayApiInterface
     */
    protected $gopayApi;


    /**
     * {@inheritDoc}
     */
    public function setApi($api) {
        if (!is_array($api)) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * @param GoPayApiInterface $gopayApi
     * @param Payum $payum
     */
    public function __construct(GoPayApiInterface $gopayApi, Payum $payum) {
        $this->gopayApi = $gopayApi;
        $this->payum = $payum;
    }


    /**
     * {@inheritDoc}
     */
    public function execute($request) {
        RequestNotSupportedException::assertSupports($this, $request);
        $goId = $this->api['goid'];
        $clientId = $this->api['clientId'];
        $clientSecret = $this->api['clientSecret'];
        $environment = $this->api['isProductionMode'];

        $gopayApi = $this->getGoPayApi();
        $gopayApi->authorize($goId, $clientId, $clientSecret, $environment);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        /**
         * Not new order
         */
        if (null !== $model['orderId'] && null !== $model['externalPaymentId']) {
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

            return;
        }


        /**
         * New order
         */
        
        /** @var TokenInterface */
        $token = $request->getToken();
        $order = $this->prepareOrder($token, $model, $goId);
        $response = $gopayApi->create($order);

        if ($response && GoPayApiInterface::CREATED === $response->json['state']) {
            $model['orderId'] = $response->json['order_number'];
            $model['externalPaymentId'] = $response->json['id'];
            $request->setModel($model);

            throw new HttpRedirect($response->json['gw_url']);
        }

        throw \RuntimeException::newInstance($response->__toString());
    }

    /**
     * @param mixed $request
     * @return boolean
     */
    public function supports($request) {
        return
            $request instanceof SetGoPay &&
            $request->getModel() instanceof \ArrayObject;
    }


    /**
     * @return GoPayApiInterface
     */
    public function getGoPayApi() {
        return $this->gopayApi;
    }


    /**
     * @param GoPayApiInterface $gopayApi
     */
    public function setGoPayApi($gopayApi) {
        $this->gopayApi = $gopayApi;
    }


    /**
     * Prepare order body for GoPay request
     * @param  TokenInterface   $token
     * @param  mixed            $model
     * @param  string           $goid
     * @return []
     */
    private function prepareOrder(TokenInterface $token, $model, $goid) {
        $notifyToken = $this->createNotifyToken($token->getGatewayName(), $token->getDetails());

        $order = [];
        $order['target']['type'] = 'ACCOUNT';
        $order['target']['goid'] = $goid;
        $order['currency'] = $model['currencyCode'];
        $order['amount'] = $model['totalAmount'];
        $order['order_number'] = $model['extOrderId'];
        $order['lang'] = Language::CZECH;

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
    

    /**
     * @param $model
     * @return []
     */
    private function resolveProducts($model) {
        if (!array_key_exists('items', $model) || count($model['items']) === 0) {
            return [
                [
                    'name' => $model['description'],
                    'amount' => $model['totalAmount']
                ]
            ];
        }
    }


    /**
     * @param string $gatewayName
     * @param object $model
     *
     * @return TokenInterface
     */
    private function createNotifyToken($gatewayName, $model) {
        return $this->payum->getTokenFactory()->createNotifyToken(
            $gatewayName,
            $model
        );
    }
}
