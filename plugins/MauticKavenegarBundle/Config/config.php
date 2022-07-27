<?php

namespace MauticPlugin\MauticKavenegarBundle;

return [
    'version' => '1.0.0',
    'services' => [
        'events' => [
            'mautic_integration.mautickavenegar.service.sms.subscriber.stop' => [
                'class'     =>  Integration\EventListener\StopSubscriber::class,
                'arguments' => [
                    'mautic.lead.model.dnc',
                    'mautic.mautickavenegar.configuration',
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
