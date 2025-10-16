<?php

/**
 * Email Configuration Script for SaleMitra
 * Run this script to quickly configure email settings
 */

echo "📧 SaleMitra Email Configuration\n";
echo "================================\n\n";

// Read current .env file
$envFile = '.env';
$envContent = file_exists($envFile) ? file_get_contents($envFile) : '';

// Email configuration options
$emailConfigs = [
    'gmail' => [
        'MAIL_MAILER' => 'smtp',
        'MAIL_HOST' => 'smtp.gmail.com',
        'MAIL_PORT' => '587',
        'MAIL_USERNAME' => '',
        'MAIL_PASSWORD' => '',
        'MAIL_ENCRYPTION' => 'tls',
        'MAIL_FROM_ADDRESS' => '',
        'MAIL_FROM_NAME' => 'SaleMitra',
    ],
    'outlook' => [
        'MAIL_MAILER' => 'smtp',
        'MAIL_HOST' => 'smtp-mail.outlook.com',
        'MAIL_PORT' => '587',
        'MAIL_USERNAME' => '',
        'MAIL_PASSWORD' => '',
        'MAIL_ENCRYPTION' => 'tls',
        'MAIL_FROM_ADDRESS' => '',
        'MAIL_FROM_NAME' => 'SaleMitra',
    ],
    'sendgrid' => [
        'MAIL_MAILER' => 'smtp',
        'MAIL_HOST' => 'smtp.sendgrid.net',
        'MAIL_PORT' => '587',
        'MAIL_USERNAME' => 'apikey',
        'MAIL_PASSWORD' => '',
        'MAIL_ENCRYPTION' => 'tls',
        'MAIL_FROM_ADDRESS' => '',
        'MAIL_FROM_NAME' => 'SaleMitra',
    ],
    'log' => [
        'MAIL_MAILER' => 'log',
        'MAIL_HOST' => '127.0.0.1',
        'MAIL_PORT' => '2525',
        'MAIL_USERNAME' => 'null',
        'MAIL_PASSWORD' => 'null',
        'MAIL_ENCRYPTION' => 'null',
        'MAIL_FROM_ADDRESS' => 'hello@example.com',
        'MAIL_FROM_NAME' => 'SaleMitra',
    ]
];

echo "Available email providers:\n";
echo "1. Gmail\n";
echo "2. Outlook/Hotmail\n";
echo "3. SendGrid\n";
echo "4. Log (for testing)\n\n";

$choice = readline("Select provider (1-4): ");

$providers = ['gmail', 'outlook', 'sendgrid', 'log'];
if (!isset($providers[$choice - 1])) {
    echo "Invalid choice. Exiting.\n";
    exit(1);
}

$provider = $providers[$choice - 1];
$config = $emailConfigs[$provider];

echo "\nConfiguring {$provider}...\n\n";

// Get user input for required fields
if ($provider !== 'log') {
    $config['MAIL_USERNAME'] = readline("Email address: ");
    $config['MAIL_PASSWORD'] = readline("Password/API Key: ");
    $config['MAIL_FROM_ADDRESS'] = $config['MAIL_USERNAME'];
}

// Update .env file
foreach ($config as $key => $value) {
    $pattern = "/^{$key}=.*/m";
    $replacement = "{$key}={$value}";
    
    if (preg_match($pattern, $envContent)) {
        $envContent = preg_replace($pattern, $replacement, $envContent);
    } else {
        $envContent .= "\n{$replacement}";
    }
}

// Write updated .env file
file_put_contents($envFile, $envContent);

echo "\n✅ Email configuration updated!\n";
echo "📧 Provider: {$provider}\n";
echo "📤 From: {$config['MAIL_FROM_ADDRESS']}\n\n";

echo "Next steps:\n";
echo "1. Test your configuration: php artisan email:test your-email@example.com\n";
echo "2. Check the EMAIL_SETUP_GUIDE.md for detailed instructions\n";
echo "3. Configure queue worker: php artisan queue:work\n\n";

echo "🎉 Email system is ready!\n";
