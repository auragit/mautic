<?php

namespace MauticPlugin\MauticKavenegarBundle\Integration\EventListener;

use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Model\DoNotContact as DoNotContactModel;
use Mautic\SmsBundle\Event\ReplyEvent;
use Mautic\SmsBundle\SmsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MauticPlugin\MauticKavenegarBundle\Integration\SmsGateway\Configuration;

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

    /**
     * StopSubscriber constructor.
     */
    public function __construct(DoNotContactModel $doNotContactModel, Configuration $configuration)
    {
        $this->doNotContactModel         = $doNotContactModel;
        $this->configuration = $configuration;
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

                // Unsubscribe the contact
                $this->doNotContactModel->addDncForContact($event->getContact()->getId(), 'sms', DoNotContact::UNSUBSCRIBED);
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
