<?php

namespace Czende\GoPayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class GoPayGatewayConfigurationType extends AbstractType {
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('goid', TextType::class, [
                'label' => 'czende.gopay_plugin.goid',
                'constraints' => [
                    new NotBlank([
                        'message' => 'czende.gopay_plugin.gateway_configuration.goid.not_blank',
                    ])
                ],
            ])
            ->add('clientId', TextType::class, [
                'label' => 'czende.gopay_plugin.clientId',
                'constraints' => [
                    new NotBlank([
                        'message' => 'czende.gopay_plugin.gateway_configuration.clientId.not_blank',
                    ])
                ],
            ])
            ->add('clientSecret', TextType::class, [
                'label' => 'czende.gopay_plugin.clientSecret',
                'constraints' => [
                    new NotBlank([
                        'message' => 'czende.gopay_plugin.gateway_configuration.clientSecret.not_blank',
                    ])
                ],
            ])
            ->add('isProductionMode', CheckboxType::class, [
                'label' => 'czende.gopay_plugin.clientSecret'
            ]);
    }
}
