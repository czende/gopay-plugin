<?php

namespace Czende\GoPayPlugin\Exception;

use Payum\Core\Exception\Http\HttpException;

class GoPayException extends HttpException {
    const LABEL = 'GoPayException';

    public static function newInstance($status) {
        $parts = [self::LABEL];

        if (property_exists($status, 'statusLiteral')) {
            $parts[] = '[reason literal] ' . $status->statusLiteral;
        }

        if (property_exists($status, 'statusCode')) {
            $parts[] = '[status code] ' . $status->statusCode;
        }

        if (property_exists($status, 'statusDesc')) {
            $parts[] = '[reason phrase] ' . $status->statusDesc;
        }

        $message = implode(PHP_EOL, $parts);

        $e = new static($message);

        return $e;
    }
}
