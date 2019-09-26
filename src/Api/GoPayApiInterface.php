<?php
declare(strict_types=1);

namespace Bratiask\GoPayPlugin\Api;

use GoPay\Http\Response;

interface GoPayApiInterface
{
    const CREATED = 'CREATED';
    const PAID = 'PAID';
    const CANCELED = 'CANCELED';
    const TIMEOUTED = 'TIMEOUTED';

    public function authorize(string $goId, string $clientId, string $clientSecret, bool $isProductionMode): void;

    public function create(array $order): Response;

    public function retrieve(int $paymentId): Response;
}
