<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticKavenegarBundle\Controller\Api;

use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Mautic\LeadBundle\Entity\LeadEventLog;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadEventLogRepository;
use MauticPlugin\MauticKavenegarBundle\Integration\MauticKavenegarIntegration;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Mautic\SmsBundle\Entity\Stat;
use Mautic\SmsBundle\Entity\StatRepository;
use MauticPlugin\MauticKavenegarBundle\Event\SmsDeliveryEvent;
use MauticPlugin\MauticKavenegarBundle\Integration\SmsGateway\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class SmsApiController.
 */
class DeliveryController extends AbstractController
{
    public const ON_DELIVER = 'mautic.sms.on_deliver';

    private $configuration;

    private $statRepository;

    private $leadEventLogRepository;

    public function __construct(
        Configuration $configuration,
        StatRepository $statRepository,
        LeadEventLogRepository $leadEventLogRepository,
    ) {
        $this->configuration = $configuration;
        $this->statRepository = $statRepository;
        $this->leadEventLogRepository = $leadEventLogRepository;
    }


    /**
     * Obtains a list of emails.
     *
     * @return Response
     */
    public function deliverAction(Request $request, $path)
    {

        if ($this->configuration->getPublicApiPath() !== $path) {
            throw new AccessDeniedHttpException();
        }

        $status = $request->get('status');

        if (10 == $status) { //delivered

            $messageid = $request->get('messageid');

            // $em = $this->factory->getEntityManager();
            // $repo = $em->getRepository('MauticSmsBundle:Stat');
            $stat = $this->statRepository->findOneBy(array('refid' => $messageid, 'dateDelivered' => null));

            if (isset($stat)) {
                $stat->setDateDelivered(new DateTime());
                $this->statRepository->saveEntity($stat);

                $lead = $stat->getLead();

                $this->broadcastEvent($lead, $stat);

                return new Response('');
            } else { // was not found!
                // error_log("message id was delivered, but it's not on db: " . $messageid);
                return new Response("message not found or is already delivered.", 400);
            }
        }

        return new Response('ignoring..');
    }

    private function broadcastEvent(Lead $lead, Stat $stat)
    {
        $log = new LeadEventLog();
        $log->setLead($lead)
            ->setBundle(MauticKavenegarIntegration::NAME)
            ->setObject('sms')
            ->setUserId(0)
            ->setUserName('System')
            ->setAction('deliver')
            ->setProperties(
                [
                    'smsId' => $stat->getSms()->getId(),
                    'statId' => $stat->getId(),
                    'message' => 'SMS successfuly delivered to the contact.',
                ]
            );

        $this->leadEventLogRepository->saveEntity($log);
        $this->leadEventLogRepository->detachEntity($log);


        $deliveryEvent = new SmsDeliveryEvent($lead, $stat);
        $dispatcher = new EventDispatcher();
        $dispatcher->dispatch(DeliveryController::ON_DELIVER, $deliveryEvent);
    }
}
