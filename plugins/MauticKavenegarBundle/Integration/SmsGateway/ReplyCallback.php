<?php

namespace MauticPlugin\MauticKavenegarBundle\Integration\SmsGateway;

use Mautic\SmsBundle\Callback\CallbackInterface;
use Mautic\SmsBundle\Exception\NumberNotFoundException;
use Mautic\SmsBundle\Helper\ContactHelper;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use MauticPlugin\MauticKavenegarBundle\Integration\SmsGateway\Configuration;

class ReplyCallback implements CallbackInterface
{
    /**
     * @var ContactHelper
     */
    private $contactHelper;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * TwilioCallback constructor.
     */
    public function __construct(ContactHelper $contactHelper, Configuration $configuration)
    {
        $this->contactHelper = $contactHelper;
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getTransportName()
    {
        $path = $this->configuration->getPublicApiPath();
        if (isset($path)) {
            return "kavenegar-${path}";
        } else {
            return 'kavenegar';
        }
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     *
     * @throws NumberNotFoundException
     */
    public function getContacts(Request $request)
    {
        $this->validateRequest($request->request);

        $number = $request->get('from');

        return $this->contactHelper->findContactsByNumber($number);
    }

    /**
     * @return string
     */
    public function getMessage(Request $request)
    {
        $this->validateRequest($request->request);
        $msg = urldecode($request->get('message'));
        return trim($msg);
    }

    private function validateRequest(ParameterBag $request)
    {

        // error_log(print_r($request, true));
        // try {
        //     $accountSid = $this->configuration->getAccountSid();
        // } catch (ConfigurationException $exception) {
        //     // Not published or not configured
        //     throw new NotFoundHttpException();
        // }

        // // Validate this is a request from Twilio
        // if ($accountSid !== $request->get('AccountSid')) {
        //     throw new BadRequestHttpException();
        // }

        // Who is the message from?
        $number = $request->get('from');
        if (empty($number)) {
            throw new BadRequestHttpException();
        }

        // What did they say?
        $message = trim($request->get('message'));
        if (empty($message)) {
            throw new BadRequestHttpException();
        }
    }
}
