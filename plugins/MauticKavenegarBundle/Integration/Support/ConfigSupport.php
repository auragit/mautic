<?php

namespace MauticPlugin\MauticKavenegarBundle\Integration\Support;

use MauticPlugin\MauticKavenegarBundle\Integration\MauticKavenegarIntegration;
use MauticPlugin\MauticKavenegarBundle\Form\Type\ConfigAuthType;
use Mautic\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;

class ConfigSupport extends MauticKavenegarIntegration implements ConfigFormInterface, ConfigFormAuthInterface
{
  use DefaultConfigFormTrait;

  public function getAuthConfigFormName(): string
  {
    return ConfigAuthType::class;
  }
}
