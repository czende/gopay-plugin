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

    // Api array
    private $api = [];

    /**
     * @var GoPayApiWrapper
     */
    private $goPayApiWrapper;

    /**
     * Set GOPAY wraper api
     */
    public function setApi($api) {
        if (!is_array($api)) {
            throw new UnsupportedApiException('Api not supported.');
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

        // if ($model['order_number'] !== null) {
        //     /** @var mixed $response */
        //     // $response = $goPayApi->retrieve($model['order_number'])->getResponse();

        //     // Assert::keyExists($response->orders, 0);
        //     dump($request);
        //     exit();
        //     if ($response->statusCode === GoPayApiWrapper::SUCCESS_API_STATUS) {

        //         $model['state'] = $response->orders[0]->statusCode;
        //         $request->setModel($model);
        //     }

        //     return;
        // }


        /**
         * @var TokenInterface $token
         */
        $token = $request->getToken();
        $order = $this->prepareOrder($token, $model, $goid);
        $response = $goPayApi->create($order);

        if ($response && $response->hasSucceed()) {
            $model['order_number'] = $response->json['order_number'];
            $request->setModel($model);

            throw new HttpRedirect($response->json['gw_url']);
        }

        throw GoPayException::newInstance($response);
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

    public function setGoPayApiWrapper($goPayApiWrapper) {
        $this->goPayApiWrapper = $goPayApiWrapper;
    }

    private function prepareOrder(TokenInterface $token, $model, $goid) {
        $order = [];
        $order['target']['type'] = 'ACCOUNT';
        $order['target']['goid'] = $goid;
        $order['currency'] = $model['currencyCode'];
        $order['amount'] = $model['totalAmount'];
        $order['order_number'] = $model['extOrderId'];
        $order['lang'] = 'CS';

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

        $order['callback']['return_url'] = $token->getAfterUrl();
        $order['callback']['notification_url'] = $token->getAfterUrl();

        return $order;
    }

    /**
     * @param $model
     * @return array
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

        return $model['items'];
    }
}