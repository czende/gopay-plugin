<?php

namespace Czende\GoPayPlugin;

use Czende\GoPayPlugin\Action\CaptureAction;
use Czende\GoPayPlugin\Action\ConvertPaymentAction;
use Czende\GoPayPlugin\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use GoPay\Definition\TokenScope;
use GoPay\Definition\Language;

class GoPayGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'gopay',
            'payum.factory_title' => 'GoPay',
            
            'payum.action.capture' => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction()
        ]);

        if (false == $config['payum.api']) {
            // Set GoPay default options
            $config['payum.default_options'] = [
                'goid' => '',
                'clientId' => '',
                'clientSecret' => '',
                'isProductionMode' => ''
            ];
            $config->defaults($config['payum.default_options']);

            // Set GoPay required fields
            $config['payum.required_options'] = ['goid', 'clientId', 'clientSecret', 'environment'];

            // Set Payum API
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $gopayconfig = [
                    'goid' => $config['goid'],
                    'clientId' => $config['clientId'],
                    'clientSecret' => $config['clientSecret'],
                    'isProductionMode' => ($config['environment'] == 'production' ? true : false),
                    'scope' => TokenScope::ALL,
                    'language' => Language::CZECH,
                    'timeout' => 30
                ];

                return $gopayconfig;
            };
        }
    }
}
