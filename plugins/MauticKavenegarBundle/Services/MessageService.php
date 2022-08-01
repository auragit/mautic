<?php

namespace MauticPlugin\MauticKavenegarBundle\Services;

use Exception;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use MauticPlugin\MauticKavenegarBundle\Integration\SmsGateway\Configuration;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Psr7\Request;

class MessageService
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(
        Client $client,
        Configuration $configuration,
        LoggerInterface $logger
    ) {
        $this->httpClient = $client;
        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    /*
  returns refid for message. if failed, returns -1.
**/
    public function sendSms(string $leadPhoneNumber, string $msg): int
    {

        try {

            $apikey = $this->configuration->getApiKey();
            $fromnumber = $this->configuration->getFromNumber();
            $url = "https://api.kavenegar.com/v1/" . $apikey . "/sms/send.json?receptor=" . $leadPhoneNumber . "&message=" . $msg . "&sender=" . $fromnumber;
            $response =  $this->httpClient->sendRequest(new Request('GET', $url));

            if (!in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
                $this->logger->error('sms_gateway.send', [
                    'response' => $response->getBody()->getContents(),
                    'phone_number' => $leadPhoneNumber,
                ]);
                return -1;
            } else { //successful http code

                /*
                  {
                      "return": {
                          "status": 200,
                          "message": "تایید شد"
                      },
                      "entries": [
                          {
                              "messageid": 90156850,
                              "message": "سلام مجدد تست میکنیم ۳۲۳",
                              "status": 1,
                              "statustext": "در صف ارسال",
                              "sender": "100002872",
                              "receptor": "09227678161",
                              "date": 1658785909,
                              "cost": 450
                          }
                      ]
                  }
              */
                $result = json_decode($response->getBody(), true);

                /* 
                  {
                      "messageid": 90156850,
                      "message": "سلام مجدد تست میکنیم ۳۲۳",
                      "status": 1,
                      "statustext": "در صف ارسال",
                      "sender": "100002872",
                      "receptor": "09227678161",
                      "date": 1658785909,
                      "cost": 450
                  }
              */
                $smsSendResult = $result['entries'][0];
                return $smsSendResult['messageid'];


                // $stat->addDetail('messageid', $messageid);
            }
        } catch (Exception $e) {
            error_log($e);
            return -1;
        }
    }
}
