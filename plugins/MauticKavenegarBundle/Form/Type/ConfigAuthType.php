<?php

declare(strict_types=1);

namespace MauticPlugin\MauticKavenegarBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigAuthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('apiKey', TextType::class, [
            'label' => 'sms-gateway.keys.api-key',
            'label_attr' => ['class' => 'control-label'],
            'required' => true,
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $builder->add('publicApiPath', TextType::class, [
            'label' => 'sms-gateway.keys.public-api-path-text',
            'label_attr' => ['class' => 'control-label'],
            'required' => false,
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $builder->add('fromNumber', TextType::class, [
            'label' => 'sms-gateway.keys.default-number',
            'label_attr' => ['class' => 'control-label'],
            'required' => false,
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $builder->add('unsubscribeRegexPattern', TextType::class, [
            'label' => 'sms-gateway.keys.unsubscribe-regex',
            'label_attr' => ['class' => 'control-label'],
            'required' => false,
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        $builder->add('unsubscribedMessage', TextType::class, [
            'label' => 'sms-gateway.keys.unsubscribed-message',
            'label_attr' => ['class' => 'control-label'],
            'required' => false,
            'attr' => [
                'class' => 'form-control',
            ],
        ]);

        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'integration' => null,
        ]);
    }
}
