<?php

declare(strict_types=1);

namespace Bratiask\GoPayPlugin\Api;

use GoPay\Http\Response;

interface GoPayApiInterface
{
    public const CREATED = 'CREATED';
    public const PAID = 'PAID';
    public const CANCELED = 'CANCELED';
    public const TIMEOUTED = 'TIMEOUTED';

    public function authorize(string $goId, string $clientId, string $clientSecret, bool $isProductionMode, string $language): void;

    public function create(array $order): Response;

    public function retrieve(int $paymentId): Response;
}
