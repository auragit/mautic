<?php

namespace MauticPlugin\MauticKavenegarBundle\Integration\SmsGateway;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\SmsBundle\Sms\TransportInterface;
use Psr\Log\LoggerInterface;
use Mautic\SmsBundle\Entity\Stat;
use Mautic\LeadBundle\Helper\TokenHelper;
use MauticPlugin\MauticKavenegarBundle\Services\MessageService;

class SmsGatewayTransport implements TransportInterface
{
    private LoggerInterface $logger;

    private MessageService $messageService;

    public function __construct(
        LoggerInterface $logger,
        MessageService $messageService
    ) {
        error_log("salammm dada");
        $this->logger = $logger;
        $this->messageService = $messageService;
    }

    public function sendSms(Lead $lead, $content, Stat $stat): int
    {
        $leadPhoneNumber = $lead->getLeadPhoneNumber();
        $msg = urlencode($this->contentTokenReplace($lead, $content));

        $refid = $this->messageService->sendSms($leadPhoneNumber, $msg);

        return $refid;
    }

    public function contentTokenReplace(Lead $lead, string $content)
    {
        $tokens = array_merge(
            TokenHelper::findLeadTokens($content, $lead->getProfileFields()),
        );

        return str_replace(array_keys($tokens), array_values($tokens), $content);
    }
}
