<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class SSLService
{
    /**
     * Check SSL certificate status
     */
    public function checkSSLStatus(string $domain = null): array
    {
        try {
            $domain = $domain ?: config('app.url');
            $domain = parse_url($domain, PHP_URL_HOST);
            
            $context = stream_context_create([
                "ssl" => [
                    "capture_peer_cert" => true,
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ],
            ]);
            
            $socket = stream_socket_client(
                "ssl://{$domain}:443",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if (!$socket) {
                throw new \Exception("Failed to connect to {$domain}: {$errstr}");
            }
            
            $cert = stream_context_get_params($socket)['options']['ssl']['peer_certificate'];
            $certInfo = openssl_x509_parse($cert);
            
            fclose($socket);
            
            return [
                'status' => 'valid',
                'domain' => $domain,
                'issuer' => $certInfo['issuer']['CN'] ?? 'Unknown',
                'subject' => $certInfo['subject']['CN'] ?? 'Unknown',
                'valid_from' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t']),
                'valid_to' => date('Y-m-d H:i:s', $certInfo['validTo_time_t']),
                'days_until_expiry' => $this->getDaysUntilExpiry($certInfo['validTo_time_t']),
                'is_expired' => $certInfo['validTo_time_t'] < time(),
                'is_expiring_soon' => $this->isExpiringSoon($certInfo['validTo_time_t']),
            ];
            
        } catch (\Exception $e) {
            Log::error("SSL check failed for {$domain}: " . $e->getMessage());
            
            return [
                'status' => 'error',
                'domain' => $domain,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate SSL certificate using Let's Encrypt
     */
    public function generateLetsEncryptCertificate(string $domain, array $email = []): array
    {
        try {
            $email = $email ?: [config('mail.from.address')];
            
            // Check if certbot is installed
            $certbotCheck = Process::run('which certbot');
            if (!$certbotCheck->successful()) {
                throw new \Exception('Certbot is not installed. Please install certbot first.');
            }
            
            // Generate certificate
            $command = sprintf(
                'certbot certonly --webroot -w %s -d %s --email %s --agree-tos --non-interactive',
                public_path(),
                $domain,
                implode(',', $email)
            );
            
            $result = Process::run($command);
            
            if ($result->successful()) {
                Log::info("SSL certificate generated successfully for {$domain}");
                
                return [
                    'success' => true,
                    'domain' => $domain,
                    'message' => 'SSL certificate generated successfully',
                    'certificate_path' => "/etc/letsencrypt/live/{$domain}/fullchain.pem",
                    'private_key_path' => "/etc/letsencrypt/live/{$domain}/privkey.pem",
                ];
            } else {
                throw new \Exception("Certificate generation failed: " . $result->errorOutput());
            }
            
        } catch (\Exception $e) {
            Log::error("SSL certificate generation failed for {$domain}: " . $e->getMessage());
            
            return [
                'success' => false,
                'domain' => $domain,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Renew SSL certificate
     */
    public function renewSSLCertificate(string $domain = null): array
    {
        try {
            $command = $domain 
                ? "certbot renew --cert-name {$domain}"
                : 'certbot renew';
            
            $result = Process::run($command);
            
            if ($result->successful()) {
                Log::info("SSL certificate renewed successfully");
                
                return [
                    'success' => true,
                    'message' => 'SSL certificate renewed successfully',
                ];
            } else {
                throw new \Exception("Certificate renewal failed: " . $result->errorOutput());
            }
            
        } catch (\Exception $e) {
            Log::error("SSL certificate renewal failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Configure web server for SSL
     */
    public function configureWebServerSSL(string $domain, string $webServer = 'nginx'): array
    {
        try {
            $config = $this->generateSSLConfig($domain, $webServer);
            
            $configPath = $this->getConfigPath($webServer, $domain);
            
            // Backup existing configuration
            $this->backupConfig($configPath);
            
            // Write new configuration
            file_put_contents($configPath, $config);
            
            // Test configuration
            $testResult = $this->testConfig($webServer);
            
            if ($testResult['success']) {
                // Reload web server
                $this->reloadWebServer($webServer);
                
                Log::info("SSL configuration updated successfully for {$domain}");
                
                return [
                    'success' => true,
                    'domain' => $domain,
                    'web_server' => $webServer,
                    'config_path' => $configPath,
                    'message' => 'SSL configuration updated successfully',
                ];
            } else {
                throw new \Exception("Configuration test failed: " . $testResult['error']);
            }
            
        } catch (\Exception $e) {
            Log::error("SSL configuration failed for {$domain}: " . $e->getMessage());
            
            return [
                'success' => false,
                'domain' => $domain,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Force HTTPS redirect
     */
    public function forceHTTPSRedirect(): array
    {
        try {
            // Update .htaccess for Apache
            $htaccessPath = public_path('.htaccess');
            $htaccessContent = $this->generateHTAccessSSL();
            
            file_put_contents($htaccessPath, $htaccessContent);
            
            // Update Laravel configuration
            $this->updateLaravelSSLConfig();
            
            Log::info("HTTPS redirect configured successfully");
            
            return [
                'success' => true,
                'message' => 'HTTPS redirect configured successfully',
            ];
            
        } catch (\Exception $e) {
            Log::error("HTTPS redirect configuration failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get SSL recommendations
     */
    public function getSSLRecommendations(): array
    {
        return [
            'enable_hsts' => 'Enable HTTP Strict Transport Security (HSTS)',
            'use_strong_ciphers' => 'Configure strong SSL/TLS ciphers',
            'disable_weak_protocols' => 'Disable SSLv2, SSLv3, and TLS 1.0',
            'enable_ocsp_stapling' => 'Enable OCSP stapling for better performance',
            'use_perfect_forward_secrecy' => 'Enable Perfect Forward Secrecy (PFS)',
            'regular_certificate_renewal' => 'Set up automatic certificate renewal',
            'monitor_certificate_expiry' => 'Monitor certificate expiry dates',
            'use_certificate_transparency' => 'Enable Certificate Transparency logs',
        ];
    }

    /**
     * Monitor SSL certificate expiry
     */
    public function monitorCertificateExpiry(): array
    {
        $domains = $this->getMonitoredDomains();
        $results = [];
        
        foreach ($domains as $domain) {
            $status = $this->checkSSLStatus($domain);
            $results[$domain] = $status;
            
            // Alert if certificate is expiring soon
            if (isset($status['days_until_expiry']) && $status['days_until_expiry'] <= 30) {
                $this->sendExpiryAlert($domain, $status['days_until_expiry']);
            }
        }
        
        return $results;
    }

    /**
     * Get days until certificate expiry
     */
    private function getDaysUntilExpiry(int $validTo): int
    {
        $expiryDate = \Carbon\Carbon::createFromTimestamp($validTo);
        $now = \Carbon\Carbon::now();
        
        return $now->diffInDays($expiryDate, false);
    }

    /**
     * Check if certificate is expiring soon
     */
    private function isExpiringSoon(int $validTo): bool
    {
        return $this->getDaysUntilExpiry($validTo) <= 30;
    }

    /**
     * Generate SSL configuration for web server
     */
    private function generateSSLConfig(string $domain, string $webServer): string
    {
        if ($webServer === 'nginx') {
            return $this->generateNginxSSLConfig($domain);
        } elseif ($webServer === 'apache') {
            return $this->generateApacheSSLConfig($domain);
        }
        
        throw new \Exception("Unsupported web server: {$webServer}");
    }

    /**
     * Generate Nginx SSL configuration
     */
    private function generateNginxSSLConfig(string $domain): string
    {
        return "
server {
    listen 80;
    server_name {$domain};
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name {$domain};
    
    root " . public_path() . ";
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/{$domain}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/{$domain}/privkey.pem;
    
    # SSL Security
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # HSTS
    add_header Strict-Transport-Security \"max-age=31536000; includeSubDomains; preload\" always;
    
    # Security Headers
    add_header X-Frame-Options \"SAMEORIGIN\" always;
    add_header X-Content-Type-Options \"nosniff\" always;
    add_header X-XSS-Protection \"1; mode=block\" always;
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
";
    }

    /**
     * Generate Apache SSL configuration
     */
    private function generateApacheSSLConfig(string $domain): string
    {
        return "
<VirtualHost *:80>
    ServerName {$domain}
    Redirect permanent / https://{$domain}/
</VirtualHost>

<VirtualHost *:443>
    ServerName {$domain}
    DocumentRoot " . public_path() . "
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/{$domain}/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/{$domain}/privkey.pem
    
    # SSL Security
    SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384
    SSLHonorCipherOrder off
    SSLSessionTickets off
    
    # HSTS
    Header always set Strict-Transport-Security \"max-age=31536000; includeSubDomains; preload\"
    
    # Security Headers
    Header always set X-Frame-Options \"SAMEORIGIN\"
    Header always set X-Content-Type-Options \"nosniff\"
    Header always set X-XSS-Protection \"1; mode=block\"
    
    <Directory " . public_path() . ">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
";
    }

    /**
     * Generate .htaccess SSL configuration
     */
    private function generateHTAccessSSL(): string
    {
        return "
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security \"max-age=31536000; includeSubDomains; preload\"
    Header always set X-Frame-Options \"SAMEORIGIN\"
    Header always set X-Content-Type-Options \"nosniff\"
    Header always set X-XSS-Protection \"1; mode=block\"
</IfModule>

# Laravel Configuration
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/\$1 [L]
</IfModule>
";
    }

    /**
     * Get configuration file path
     */
    private function getConfigPath(string $webServer, string $domain): string
    {
        if ($webServer === 'nginx') {
            return "/etc/nginx/sites-available/{$domain}";
        } elseif ($webServer === 'apache') {
            return "/etc/apache2/sites-available/{$domain}.conf";
        }
        
        throw new \Exception("Unsupported web server: {$webServer}");
    }

    /**
     * Backup existing configuration
     */
    private function backupConfig(string $configPath): void
    {
        if (file_exists($configPath)) {
            $backupPath = $configPath . '.backup.' . date('Y-m-d_H-i-s');
            copy($configPath, $backupPath);
        }
    }

    /**
     * Test web server configuration
     */
    private function testConfig(string $webServer): array
    {
        try {
            if ($webServer === 'nginx') {
                $result = Process::run('nginx -t');
            } elseif ($webServer === 'apache') {
                $result = Process::run('apache2ctl configtest');
            } else {
                throw new \Exception("Unsupported web server: {$webServer}");
            }
            
            return [
                'success' => $result->successful(),
                'error' => $result->errorOutput(),
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Reload web server
     */
    private function reloadWebServer(string $webServer): void
    {
        if ($webServer === 'nginx') {
            Process::run('systemctl reload nginx');
        } elseif ($webServer === 'apache') {
            Process::run('systemctl reload apache2');
        }
    }

    /**
     * Update Laravel SSL configuration
     */
    private function updateLaravelSSLConfig(): void
    {
        // Update app configuration to force HTTPS
        $configPath = config_path('app.php');
        $config = file_get_contents($configPath);
        
        // Update URL to use HTTPS
        $config = preg_replace(
            '/\'url\' => env\(\'APP_URL\', \'http:\/\/localhost\'\)/',
            '\'url\' => env(\'APP_URL\', \'https://localhost\')',
            $config
        );
        
        // Force HTTPS in production
        $config = preg_replace(
            '/\'force_https\' => false/',
            '\'force_https\' => env(\'APP_ENV\') === \'production\'',
            $config
        );
        
        file_put_contents($configPath, $config);
    }

    /**
     * Get monitored domains
     */
    private function getMonitoredDomains(): array
    {
        $domains = [parse_url(config('app.url'), PHP_URL_HOST)];
        
        // Add additional domains from configuration
        $additionalDomains = config('ssl.monitored_domains', []);
        $domains = array_merge($domains, $additionalDomains);
        
        return array_unique($domains);
    }

    /**
     * Send certificate expiry alert
     */
    private function sendExpiryAlert(string $domain, int $daysUntilExpiry): void
    {
        Log::warning("SSL certificate for {$domain} expires in {$daysUntilExpiry} days");
        
        // Here you would implement actual alert sending (email, Slack, etc.)
        // For now, we'll just log the warning
    }
}
