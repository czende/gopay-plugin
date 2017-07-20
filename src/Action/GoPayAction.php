<?php

namespace Czende\GoPayPlugin\Action;

use Czende\GoPayPlugin\Exception\GoPayException;
use Czende\GoPayPlugin\GoPayApiWrapper;
use Czende\GoPayPlugin\SetGoPay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Webmozart\Assert\Assert;

final class GoPayAction implements ApiAwareInterface, ActionInterface {
    private $api = [];

    /**
     * @var GoPayApiWrapper
     */
    private $goPayApiWrapper;

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
     * {@inheritDoc}
     */
    public function execute($request) {
        RequestNotSupportedException::assertSupports($this, $request);
        $goid = $this->api['goid'];
        $clientId = $this->api['clientId'];
        $clientSecret = $this->api['clientSecret'];
        $isProductionMode = $this->api['isProductionMode'];
        
        $goPayApi = $this->getGoPayApiWrapper() ? $this->getGoPayApiWrapper() : new GoPayApiWrapper($goid, $clientId, $clientSecret, $isProductionMode);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['orderId'] !== null) {
            /** @var mixed $response */
            $response = $goPayApi->retrieve($model['orderId'])->getResponse();
            Assert::keyExists($response->orders, 0);

            if ($response->status->statusCode === GoPayApiWrapper::SUCCESS_API_STATUS) {
                $model['status'] = $response->orders[0]->status;
                $request->setModel($model);
            }

            return;
        }

        /**
         * @var TokenInterface $token
         */
        $token = $request->getToken();
        $order = $this->prepareOrder($token, $model, $goid);
        $response = $goPayApi->create($order)->getResponse();

        if ($response && $response->status->statusCode === GoPayApiWrapper::SUCCESS_API_STATUS) {
            $model['orderId'] = $response->orderId;
            $request->setModel($model);

            throw new HttpRedirect($response->redirectUri);
        }

        throw GoPayException::newInstance($response->status);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request) {
        return
            $request instanceof SetGoPay &&
            $request->getModel() instanceof \ArrayObject;
    }

    /**
     * @return GoPayApiWrapper
     */
    public function getGoPayApiWrapper() {
        return $this->goPayApiWrapper;
    }

    /**
     * @param GoPayApiWrapper $goPayApiWrapper
     */
    public function setGoPayApiWrapper($goPayApiWrapper) {
        $this->goPayApiWrapper = $goPayApiWrapper;
    }

    private function prepareOrder(TokenInterface $token, $model, $goid) {
        $order = [];
        $order['continueUrl'] = $token->getTargetUrl();
        $order['customerIp'] = $model['customerIp'];
        $order['merchantPosId'] = $goid;
        $order['description'] = $model['description'];
        $order['currencyCode'] = $model['currencyCode'];
        $order['totalAmount'] = $model['totalAmount'];
        $order['extOrderId'] = $model['extOrderId'];
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

        $buyer = [
            'email' => (string)$customer->getEmail(),
            'firstName' => (string)$customer->getFirstName(),
            'lastName' => (string)$customer->getLastName(),
        ];

        $order['buyer'] = $buyer;
        $order['products'] = $this->resolveProducts($model);

        return $order;
    }

    /**
     * @param $model
     * @return array
     */
    private function resolveProducts($model) {
        if (!array_key_exists('products', $model) || count($model['products']) === 0) {
            return [
                [
                    'name' => $model['description'],
                    'unitPrice' => $model['totalAmount'],
                    'quantity' => 1
                ]
            ];
        }

        return $model['products'];
    }
}