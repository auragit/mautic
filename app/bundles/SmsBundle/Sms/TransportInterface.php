<?php

namespace Mautic\SmsBundle\Sms;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\SmsBundle\Entity\Stat;
interface TransportInterface
{
    /**
     * @param string $content
     *
     * @return bool
     */
    public function sendSms(Lead $lead, $content, Stat $stat);
}
