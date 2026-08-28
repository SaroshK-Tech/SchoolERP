<?php
declare(strict_types=1);

/**
 * SchoolERP - Application configuration.
 * Edit values here or override per-machine in config/local.php (git-ignored).
 */

return [
    'app' => [
        'name'    => 'SchoolERP',
        'version' => '1.0.0',
        'debug'   => true,
        'timezone'=> 'Asia/Karachi',
    ],
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'school_erp',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'session_name' => 'schoolerp_sess',
        'password_cost' => 12,
    ],
    'notifications' => [
        // Twilio for SMS
        'sms' => [
            'provider' => 'twilio',              // twilio (or 'log' to simulate)
            'twilio_sid' => '',
            'twilio_token' => '',
            'twilio_from' => '',
        ],
        // WhatsApp via Twilio WhatsApp Business or a gateway webhook
        'whatsapp' => [
            'provider' => 'twilio',
            'twilio_whatsapp_from' => '',
            'enabled' => false,
        ],
        // When true, outbound messages are logged to notifications log only
        // (never actually sent). Safe for development/testing.
        'dry_run' => true,
    ],
];
