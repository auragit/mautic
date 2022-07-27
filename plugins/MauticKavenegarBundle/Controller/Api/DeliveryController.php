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
use Mautic\ApiBundle\Controller\CommonApiController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SmsApiController.
 */
class DeliveryController extends CommonApiController
{
    /**
     * Obtains a list of emails.
     *
     * @return Response
     */
    public function deliverAction()
    {
        $status = $this->request->get('status');

        if (10 == $status) { //delivered

            $messageid = $this->request->get('messageid');

            $em = $this->factory->getEntityManager();
            $repo = $em->getRepository('MauticSmsBundle:Stat');
            $stat = $repo->findOneBy(array('refid' => $messageid, 'dateDelivered' => null));

            if (isset($stat)) {
                $stat->setDateDelivered(new DateTime());
                $repo->saveEntity($stat);

                return new Response('');
            } else { // was not found!
                // error_log("message id was delivered, but it's not on db: " . $messageid);
                return new Response("message not found or is already delivered.", 400);
            }
        }

        return new Response('ignoring..');
    }
}
