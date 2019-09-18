<?php
declare(strict_types=1);

namespace Bratiask\GoPayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class GoPayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('environment', ChoiceType::class, [
                'choices' => [
                    'bratiask.gopay_plugin.sandbox' => 'sandbox',
                    'bratiask.gopay_plugin.production' => 'production'
                ],
                'label' => 'bratiask.gopay_plugin.environment',
            ])
            ->add('goid', TextType::class, [
                'label' => 'bratiask.gopay_plugin.goid',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bratiask.gopay_plugin.gateway_configuration.goid.not_blank',
                        'groups' => ['sylius'],
                    ])
                ],
            ])
            ->add('clientId', TextType::class, [
                'label' => 'bratiask.gopay_plugin.clientId',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bratiask.gopay_plugin.gateway_configuration.clientId.not_blank',
                        'groups' => ['sylius'],
                    ])
                ],
            ])
            ->add('clientSecret', TextType::class, [
                'label' => 'bratiask.gopay_plugin.clientSecret',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bratiask.gopay_plugin.gateway_configuration.clientSecret.not_blank',
                        'groups' => ['sylius'],
                    ])
                ],
            ]);
    }
}
