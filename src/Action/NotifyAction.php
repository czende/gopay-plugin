<?php
declare(strict_types=1);

namespace Bratiask\GoPayPlugin\Action;

use ArrayObject;
use Bratiask\GoPayPlugin\Api\GoPayApiInterface;
use Exception;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

final class NotifyAction implements ActionInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    protected $gopayApi;
    private $api = [];

    public function __construct(GoPayApiInterface $gopayApi)
    {
        $this->gopayApi = $gopayApi;
    }

    public function execute($request)
    {
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

        } catch (Exception $e) {
            throw new HttpResponse($e->getMessage());
        }
    }

    public function supports($request): bool
    {
        return $request instanceof Notify && $request->getModel() instanceof ArrayObject;
    }

    public function setApi($api): void
    {
        if (!is_array($api)) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }
}
