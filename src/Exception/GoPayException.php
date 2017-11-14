<?php

namespace Czende\GoPayPlugin\Exception;

use Payum\Core\Exception\Http\HttpException;

class GoPayException extends HttpException {
    const LABEL = 'GoPayException';

    public static function newInstance($status, $message = '') {
        $output = implode(PHP_EOL, [self::LABEL . ' ' . $status . ' - ' . $message]);

        $e = new static($output);

        return $e;
    }
}
