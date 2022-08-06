<?php

namespace MauticPlugin\MauticKavenegarBundle\Integration\EventListener;

use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Model\DoNotContact as DoNotContactModel;
use Mautic\SmsBundle\Event\ReplyEvent;
use Mautic\SmsBundle\SmsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MauticPlugin\MauticKavenegarBundle\Integration\SmsGateway\Configuration;
use MauticPlugin\MauticKavenegarBundle\Services\MessageService;

class StopSubscriber implements EventSubscriberInterface
{
    /**
     * @var DoNotContactModel
     */
    private $doNotContactModel;

    /**
     * @var Configuration
     */
    private $configuration;

    private $messageService;

    /**
     * StopSubscriber constructor.
     */
    public function __construct(
        DoNotContactModel $doNotContactModel,
        Configuration $configuration,
        MessageService $messageService
    ) {
        $this->doNotContactModel = $doNotContactModel;
        $this->configuration = $configuration;
        $this->messageService = $messageService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SmsEvents::ON_REPLY => ['onReply', 0],
        ];
    }

    public function onReply(ReplyEvent $event)
    {
        $message = $event->getMessage();

        $msg = $this->englishDigits(trim(strtolower($message)));

        $regex = $this->configuration->getUnsubscribeRegexPattern();

        if (isset($regex)) {

            if (1 === preg_match("/$regex/", $msg)) {

                /* unsubscribe if it's already contactable via sms. */
                if ($this->doNotContactModel->isContactable($event->getContact(), 'sms') === DoNotContact::IS_CONTACTABLE) {

                    // Unsubscribe the contact
                    $this->doNotContactModel->addDncForContact($event->getContact()->getId(), 'sms', DoNotContact::UNSUBSCRIBED);

                    // send a sms to the contact and send unsubscribed message (e.g. your request is received.)
                    $this->messageService->sendSms($event->getContact()->getLeadPhoneNumber(), $this->configuration->getUnsubscribedMessage());
                } else {
                    echo "Already unsubscribed.";
                }
            }
        }
    }

    private function englishDigits(string $string): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];

        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

        return $englishNumbersOnly;
    }
}
