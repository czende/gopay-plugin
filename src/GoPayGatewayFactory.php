<?php

namespace Czende\GoPayPlugin;

use Czende\GoPayPlugin\Action\CaptureAction;
use Czende\GoPayPlugin\Action\ConvertPaymentAction;
use Czende\GoPayPlugin\Action\NotifyAction;
use Czende\GoPayPlugin\Action\PayUAction;
use Czende\GoPayPlugin\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class GoPayGatewayFactory extends GatewayFactory {
    
    /**
     * Set configs for Payum
     */
    protected function populateConfig(ArrayObject $config) {
        $config->defaults([
            'payum.factory_name' => 'gopay',
            'payum.factory_title' => 'GoPay',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.set_payu' => new PayUAction(),
            'payum.action.notify' => new NotifyAction()
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'goid' => '',
                'clientId' => '',
                'clientSecret' => '',
                'isProductionMode' => false
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['goid', 'clientId', 'clientSecret', 'isProductionMode'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $gopayconfig = [
                    'goid' => $config['goid'],
                    'clientId' => $config['clientId'],
                    'clientSecret' => $config['clientSecret'],
                    'isProductionMode' => $config['isProductionMode'],
                ];

                return $gopayconfig;
            };
        }
    }
}
