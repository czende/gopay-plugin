<?php
declare(strict_types=1);

namespace Bratiask\GoPayPlugin\Api;

use GoPay\Api;
use GoPay\Http\Response;
use GoPay\Payments;

final class GoPayApi implements GoPayApiInterface
{
    /**
     * @var Payments
     */
    private $gopay;

    public function authorize(string $goId, string $clientId, string $clientSecret, string $language, bool $isProductionMode): void
    {
        $this->gopay = Api::payments([
            'goid' => $goId,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'isProductionMode' => $isProductionMode,
            'language' => $language
        ]);
    }

    public function create(array $order): Response
    {
        return $this->gopay->createPayment($order);
    }

    public function retrieve(int $paymentId): Response
    {
        return $this->gopay->getStatus($paymentId);
    }
}
