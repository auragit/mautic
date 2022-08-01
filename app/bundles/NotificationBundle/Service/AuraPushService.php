<?php

namespace Mautic\NotificationBundle\Service;

use Exception;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\NotificationBundle\Entity\Notification;

class AuraPushService
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private IntegrationHelper $integrationHelper;

    public function __construct(
        Client $client,
        IntegrationHelper $integrationHelper,
        LoggerInterface $logger
    ) {
        $this->httpClient = $client;
        $this->integrationHelper = $integrationHelper;
        $this->logger = $logger;
    }

    /*
  returns refid for message. if failed, returns -1.
**/
    public function sendPush(string $cid, Notification $notification): int
    {

        try {

            $data = [];
            $title    = $notification->getHeading();
            $link      = $notification->getUrl();
            $message  = $notification->getMessage();

            $data['title'] = $title;
            $data['body'] = $message;

            if ($link) {
                $data['url'] = $link;
            }

            if ($notification->isMobile()) {
                $data['type'] = 'mobile';
            } else {
                $data['type'] = 'web';
            }

            $apiKeys    = $this->integrationHelper->getIntegrationObject('OneSignal')->getKeys();
            $endpoint      = $apiKeys['send_push_endpoint'] . "/$cid";

            $response = $this->httpClient->post(
                $endpoint,
                [
                    \GuzzleHttp\RequestOptions::HEADERS => [
                        'Content-Type'  => 'application/json',
                    ],
                    \GuzzleHttp\RequestOptions::JSON => $data,
                    \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => ['strict' => true]
                ]
            );


            error_log($response->getBody()->getContents());

            return $response->getStatusCode();
        } catch (Exception $e) {
            error_log($e);
            return -1;
        }
    }
}
