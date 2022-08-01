<?php

namespace MauticPlugin\MauticKavenegarBundle\Integration\EventListener;

use Mautic\LeadBundle\Entity\LeadEventLogRepository;
use Mautic\LeadBundle\Event\LeadTimelineEvent;
use Mautic\LeadBundle\EventListener\TimelineEventLogTrait;
use Mautic\LeadBundle\LeadEvents;
use Mautic\SmsBundle\Entity\SmsRepository;
use Mautic\SmsBundle\Entity\StatRepository;
use MauticPlugin\MauticKavenegarBundle\Integration\MauticKavenegarIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * this renders the timeline for us. the onTimelineGenerate is called before the contact timeline is generated.
 * then we call addEvents and instruct TimelineEventLogTrait to load those events with MauticKavenegarIntegration::NAME, 'sms' and action = 'deliver'.
 * it also uses a view template to render the details.  */ 
class TimelineSubscriber implements EventSubscriberInterface
{
    use TimelineEventLogTrait;

    private $statRepository;

    private $translator;

    // private $smsRepository;

    /**
     * ReplySubscriber constructor.
     */
    public function __construct(TranslatorInterface $translator,
     LeadEventLogRepository $eventLogRepository,
     StatRepository $statRepository
     )
    {
        $this->translator         = $translator;
        $this->eventLogRepository = $eventLogRepository;
        $this->statRepository = $statRepository;
        // $this->smsRepository = $smsRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::TIMELINE_ON_GENERATE => 'onTimelineGenerate',
        ];
    }

    public function onTimelineGenerate(LeadTimelineEvent $event)
    {
        $this->addEvents(
            $event,
            'sms_deliver',
            'mautic.kavenegar.sms-delivery',
            'fa-mobile',
            MauticKavenegarIntegration::NAME,
            'sms',
            'deliver',
            'MauticKavenegarBundle:Timeline:delivered.html.php'
        );
    }

    private function addEvents(LeadTimelineEvent $event, $eventType, $eventTypeName, $icon, $bundle = null, $object = null, $action = null, $contentTemplate = null)
    {
        $eventTypeName = $this->translator->trans($eventTypeName);
        $event->addEventType($eventType, $eventTypeName);

        if (!$event->isApplicable($eventType)) {
            return;
        }

        $events = $this->eventLogRepository->getEvents($event->getLead(), $bundle, $object, $action, $event->getQueryOptions());

        // Add to counter
        $event->addToCounter($eventType, $events);

        if ($event->isEngagementCount()) {
            return;
        }

        // Add the logs to the event array
        foreach ($events['results'] as $log) {

            $entry = $this->getEventEntry($log, $eventType, $eventTypeName, $icon, $contentTemplate);

            $statId = $entry['extra']['statId'];
            $stat = $this->statRepository->getEntity($statId);

            $sms = $stat->getSms();

            $entry['extra']['stat'] = $stat;
            $entry['extra']['sms'] = $sms;

            $event->addEvent(
                $entry
            );
        }
    }
}
