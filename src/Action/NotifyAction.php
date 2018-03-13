<?php

namespace Czende\GoPayPlugin\Action;

use Czende\GoPayPlugin\Api\GoPayApiInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Payum\Core\GatewayInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

/**
 * @author Jan Czernin <jan.czernin@gmail.com>
 */
final class NotifyAction implements ActionInterface, ApiAwareInterface {
    use GatewayAwareTrait;

    private $api = [];

    /**
     * @var GoPayApiInterface
     */
    protected $gopayApi;

    
    /**
     * @param GoPayApiInterface $gopayApi
     * @param Payum $payum
     */
    public function __construct(GoPayApiInterface $gopayApi) {
        $this->gopayApi = $gopayApi;
    }


    /**
     * @return GatewayInterface
     */
    public function getGateway() {
        return $this->gateway;
    }


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
     * {@inheritdoc}
     */
    public function execute($request) {
        /** @var $request Notify */
        RequestNotSupportedException::assertSupports($this, $request);
        
        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        $model = $request->getModel();

        $this->gopayApi->authorize(
            $this->api['goid'], 
            $this->api['clientId'], 
            $this->api['clientSecret'], 
            $this->api['isProductionMode']
        );

        try {
            $response = $this->gopayApi->retrieve($model['externalPaymentId']);

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
               
            throw new HttpResponse('SUCCESS');

        } catch (\Exception $e) {
            throw new HttpResponse($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Notify &&
            $request->getModel() instanceof \ArrayObject
        ;
    }
}
