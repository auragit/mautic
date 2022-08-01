<?php

namespace MauticPlugin\MauticKavenegarBundle\Integration\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Helper\TokenHelper;
use Mautic\SmsBundle\Entity\Sms;
use Mautic\SmsBundle\Event\SmsSendEvent;
use Mautic\SmsBundle\SmsEvents;
use MauticPlugin\MauticKavenegarBundle\Integration\SmsGateway\Configuration;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;

class SendSmsSubscriber implements EventSubscriberInterface
{
    const CUSTOM_SMS_ID = 21;

    // private ClientInterface $client;

    /**
     * @var Client
     */
    private Client $httpClient;

    private Configuration $configuration;

    private EntityManagerInterface $em;

    private LoggerInterface $logger;

    public function __construct(
        Client $client,
        Configuration $configuration,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->httpClient = $client;
        $this->configuration = $configuration;
        $this->em = $em;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SmsEvents::SMS_ON_SEND => 'smsOnSend',
        ];
    }

    public function smsOnSend(SmsSendEvent $event): void
    {
        $lead = $event->getLead();

        $sms = $this->checkRequiredConditionsAndGetSms($event->getSmsId());

        if ($sms) {
            $this->send($lead, $sms);
        }
    }

    private function checkRequiredConditionsAndGetSms(int $smsId): ?Sms
    {
        /** @var Sms $sms */
        $sms = $this->em->getRepository(Sms::class)->findOneBy(['id' => $smsId]);
        if (!$sms) {
            $this->logger->error('sms_gateway.not_found_message', [
                'msg' => 'Message not found',
                'sms_id' => $smsId,
            ]);

            return null;
        }

        // $this->logger->info($sms->getName());
        // error_log($sms->getName());
        // error_log($sms);
        // // error_log(print_r($sms, true));

        // $category = $sms->getCategory();
        // if (!$category) {
        //     $this->logger->error('sms_gateway.empty_category', [
        //         'msg' => 'Message category is empty',
        //         'sms_id' => $smsId,
        //     ]);

        //     return null;
        // }

        return $sms;
    }

    private function send(Lead $lead, Sms $sms): void
    {
        $leadPhoneNumber = $lead->getLeadPhoneNumber();

        if (null === $leadPhoneNumber) {
            $this->logger->error('sms_gateway.send', [
                'msg' => 'Lead phone number not found',
                'lead_id' => $lead->getId(),
            ]);

            return;
        }

        try {
            // $contentBody = [
            //     'phone_number' => $leadPhoneNumber,
            //     'category' => $sms->getCategory()->getTitle(),
            //     'currency' => $lead->rv_currency,

            // ];

            // // if ($sms->getId() == self::CUSTOM_SMS_ID) {
            // //     $contentBody['custom_sms'] = true;
            // //     $contentBody['operator_name'] = $lead->operator_name;
            // // }

            $msg = urlencode($this->contentTokenReplace($lead, $sms->getMessage()));
            $apikey = $this->configuration->getapiKey();
            $fromnumber = $this->configuration->getFromNumber();
            $url = "https://api.kavenegar.com/v1/" . $apikey . "/sms/send.json?receptor=" . $leadPhoneNumber . "&message=" . $msg . "&sender=" . $fromnumber;

            $response =  $this->httpClient->sendRequest(new Request('GET', $url));

            if (!in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
                $this->logger->error('sms_gateway.send', [
                    'response' => $response->getBody()->getContents(),
                    'phone_number' => $leadPhoneNumber,
                ]);
            }
        } catch (Exception $e) {
            $this->logger->error('sms_gateway.send', [
                'msg' => $e->getMessage(),
                'phone_number' => $leadPhoneNumber,
            ]);
        }
    }

    /**
     * @param Lead $lead
     *
     * @return string|null
     *
     * @throws NumberParseException
     */
    private function getLeadPhoneNumber(Lead $lead): ?string
    {
        $number = $lead->getLeadPhoneNumber();
        if (!$number) {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();
        $parsed = $util->parse($number, 'US');

        return $util->format($parsed, PhoneNumberFormat::E164);
    }

    public function contentTokenReplace(Lead $lead, string $content)
    {
        $tokens = array_merge(
            TokenHelper::findLeadTokens($content, $lead->getProfileFields())
        );

        return str_replace(array_keys($tokens), array_values($tokens), $content);
    }
}
