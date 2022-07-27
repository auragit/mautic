<?php

namespace MauticPlugin\MauticKavenegarBundle;

/**
 * DELIVERY ENDPOINT IS: https://mautic.ddev.site/kavenegar/delivery/<PUBLIC_API_PATH>
 * REPLY ENDPOINT IS: https://mautic.ddev.site/sms/kavenegar-<PUBLIC_API_PATH>/callback
 * 
 * 
 *  don't blame me for reply endpoint, I'm using native SMSbundle's callback interface. I could however, re-implement it, but it's only about the endpoint structure so I leave it as is for now. does not differ in performance /quality.
 */
return [
    'version' => '1.0.0',
    'routes' => [
        'public' => [
            'mautickavenegar.delivery.endpoint' => [
                'path'       => '/kavenegar/delivery/{path}',
                'controller' => 'MauticKavenegarBundle:Api\Delivery:deliver', //this uses the controller we have in service section. (because we need DI)
            ],
        ]
    ],
    'services' => [
        'controllers' => [
            'mautickavenegar.delivery.controller' => [
                'class'     => Controller\Api\DeliveryController::class, // \Mautic\SmsBundle\Controller\ReplyController::class,
                'arguments' => [
                    'mautic.mautickavenegar.configuration',
                    'mautic.sms.repository.stat',
                    'mautic.lead.repository.lead_event_log',
                ],
            ],
        ],
        'events' => [
            'mautic_integration.mautickavenegar.service.sms.subscriber.stop' => [
                'class'     =>  Integration\EventListener\StopSubscriber::class,
                'arguments' => [
                    'mautic.lead.model.dnc',
                    'mautic.mautickavenegar.configuration',
                ],
            ],
            'mautic_integration.mautickavenegar.service.sms.subscriber.timeline' => [
                'class'     => Integration\EventListener\TimelineSubscriber::class,
                'arguments' => [
                    'translator',
                    'mautic.lead.repository.lead_event_log',
                    'mautic.sms.repository.stat',
                ],
            ],
        ],
        'integrations' => [
            'mautic.integration.mautickavenegar' => [
                'class' => Integration\MauticKavenegarIntegration::class,
                'tags' => [
                    'mautic.integration',
                    'mautic.basic_integration',
                ],
            ],
            'mautickavenegar.integration.configuration' => [
                'class' => Integration\Support\ConfigSupport::class,
                'tags' => [
                    'mautic.config_integration',
                ],
            ],
        ],
        'others' => [
            'mautic.mautickavenegar.callback' => [
                'class'     => Integration\SmsGateway\ReplyCallback::class, // \Mautic\SmsBundle\Integration\Twilio\TwilioCallback::class,
                'arguments' => [
                    'mautic.sms.helper.contact',
                    'mautic.mautickavenegar.configuration'
                ],
                'tag'   => 'mautic.sms_callback_handler',
            ],
            'mautic.sms.transport.mautickavenegar' => [
                'class' => Integration\SmsGateway\SmsGatewayTransport::class,
                'arguments' => [
                    'monolog.logger.mautic',
                    'mautickavenegar.service'
                ],
                'tag' => 'mautic.sms_transport',
                'tagArguments' => [
                    'integrationAlias' => 'MauticKavenegar',
                ],
                // 'alias' => 'mautic.sms.config.transport.mautickavenegar',
            ],
            'mautic.mautickavenegar.configuration' => [
                'class' => Integration\SmsGateway\Configuration::class,
                'arguments' => [
                    'mautic.helper.integration',
                    'mautic.helper.encryption',
                ]
            ],
            'mautickavenegar.service' => [
                'class' => Services\MessageService::class,
                'arguments' => [
                    'mautic.http.client',
                    'mautic.mautickavenegar.configuration',
                    'monolog.logger.mautic',
                ]
            ],
        ],
    ],
    'parameters' => [
        'sms_transport' => 'mautic.sms.transport.mautickavenegar',
    ],
];
