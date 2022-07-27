<?php

namespace MauticPlugin\MauticKavenegarBundle\Helpers;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Helper\TokenHelper;
use Mautic\IntegrationsBundle\Helper\IntegrationsHelper;
use MauticPlugin\MauticKavenegarBundle\Integration\MauticKavenegarIntegration;
use Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Mautic\PluginBundle\Entity\Integration;

class MessageHelper
{
  /**
   * @var MauticKavenegarIntegration
   */
  private $integration;

  public function __construct(IntegrationsHelper $integrationsHelper)
  {
    $this->integration = $integrationsHelper->getIntegration(MauticKavenegarIntegration::NAME);
  }

  private function getIntegrationEntity(): Integration
  {
    return $this->integration->getIntegrationConfiguration();
  }

  public function getKeys(): array
  {
    try {
      $integration = $this->getIntegrationEntity();

      return array_merge([
        'isPublished' =>  (bool)$integration->getIsPublished()
      ], $integration->getApiKeys());
    } catch (IntegrationNotFoundException $e) {
      return [];
    }
  }

  private function formatProfileFields(Lead $lead, bool $shortenUrls)
  {
    $leadFields = $lead->getProfileFields();

    if ($shortenUrls) {
      foreach ($leadFields as $key => $value) {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
          $leadFields[$key] = str_replace([$value], ['[[' . $value . ']]'], $value);
        }
      }
    }

    return $leadFields;
  }

  public function getMessageText(Lead $lead, string $message, bool $shortenUrls)
  {
    $formattedFields = $this->formatProfileFields($lead, $shortenUrls);
    $message = TokenHelper::findLeadTokens($message, $formattedFields, true);
    return $message;
  }
}
