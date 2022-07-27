<?php

namespace MauticPlugin\MauticKavenegarBundle\Event;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\SmsBundle\Entity\Stat;
use Symfony\Contracts\EventDispatcher\Event;

class SmsDeliveryEvent extends Event
{
    /**
     * @var Lead
     */
    private $contact;

    /**
     * @var Stat
     */
    private $stat;

    /**
     * ReplyEvent constructor.
     *
     * @param string $message
     */
    public function __construct(Lead $contact, Stat $stat)
    {
        $this->contact = $contact;
        $this->stat = $stat;
    }

    /**
     * @return Lead
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return string
     */
    public function getStat()
    {
        return $this->stat;
    }

}
