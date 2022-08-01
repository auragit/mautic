<?php

namespace MauticPlugin\MauticKavenegarBundle\Integration;

use Mautic\IntegrationsBundle\Integration\BasicIntegration;
use Mautic\IntegrationsBundle\Integration\ConfigurationTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\BasicInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormFeaturesInterface;

class MauticKavenegarIntegration extends BasicIntegration implements BasicInterface, ConfigFormFeaturesInterface
{
  use ConfigurationTrait;

  const DISPLAY_NAME = 'Kavenegar';
  const NAME = 'MauticKavenegar';

  public function getDisplayName(): string
  {
    return self::DISPLAY_NAME;
  }

  public function getName(): string
  {
    return self::NAME;
  }

  public function getIcon(): string
  {
    return 'plugins/MauticKavenegarBundle/Assets/img/icon.png';
  }

  /**
   * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
   * @param array                                             $data
   * @param string                                            $formArea
   */
  public function appendToForm(&$builder, $data, $formArea)
  {
    if ('features' == $formArea) {

      // $builder->add('fromNumber', TextType::class, [
      //   'label' => 'sms-gateway.keys.default-number',
      //   'label_attr' => ['class' => 'control-label'],
      //   'required' => false,
      //   'attr' => [
      //     'class' => 'form-control',
      //   ],
      // ]);

      // $builder->add('unsubscribeRegexPattern', TextType::class, [
      //   'label' => 'sms-gateway.keys.unsubscribe-regex',
      //   'label_attr' => ['class' => 'control-label'],
      //   'required' => false,
      //   'attr' => [
      //     'class' => 'form-control',
      //   ],
      // ]);
    }
  }
}
