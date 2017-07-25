<?php

namespace Czende\GoPayPlugin\Exception;

use Payum\Core\Exception\Http\HttpException;

class GoPayException extends HttpException {
    const LABEL = 'GoPayException';

    public static function newInstance($status) {
        $message = implode(PHP_EOL, [self::LABEL . ' ' . $status]);

        $e = new static($message);

        return $e;
    }
}
