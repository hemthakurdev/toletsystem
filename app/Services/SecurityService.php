<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SecurityService
{
    /**
     * Generate secure API token
     */
    public function generateSecureToken(int $length = 64): string
    {
        return Str::random($length);
    }

    /**
     * Hash sensitive data
     */
    public function hashSensitiveData(string $data): string
    {
        return Hash::make($data);
    }

    /**
     * Verify sensitive data
     */
    public function verifySensitiveData(string $data, string $hash): bool
    {
        return Hash::check($data, $hash);
    }

    /**
     * Sanitize input data
     */
    public function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove potentially dangerous characters
                $value = strip_tags($value);
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                $value = trim($value);
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }

    /**
     * Validate file upload security
     */
    public function validateFileUpload($file, array $allowedTypes = [], int $maxSize = 5242880): array
    {
        $errors = [];
        
        // Check file size
        if ($file->getSize() > $maxSize) {
            $errors[] = "File size exceeds maximum allowed size of " . ($maxSize / 1024 / 1024) . "MB";
        }
        
        // Check file type
        if (!empty($allowedTypes) && !in_array($file->getMimeType(), $allowedTypes)) {
            $errors[] = "File type not allowed. Allowed types: " . implode(', ', $allowedTypes);
        }
        
        // Check for malicious content
        $content = file_get_contents($file->getPathname());
        if ($this->containsMaliciousContent($content)) {
            $errors[] = "File contains potentially malicious content";
        }
        
        return $errors;
    }

    /**
     * Check for malicious content in files
     */
    private function containsMaliciousContent(string $content): bool
    {
        $maliciousPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/eval\(/i',
            '/expression\(/i',
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Generate secure password
     */
    public function generateSecurePassword(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        $all = $uppercase . $lowercase . $numbers . $symbols;
        
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }
        
        return str_shuffle($password);
    }

    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return $errors;
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        Log::channel('security')->warning($event, array_merge([
            'timestamp' => now()->toISOString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ], $context));
    }

    /**
     * Check for suspicious activity
     */
    public function checkSuspiciousActivity(string $ip, string $action): bool
    {
        $key = "suspicious_activity:{$ip}:{$action}";
        $attempts = Cache::get($key, 0);
        
        if ($attempts > 10) {
            $this->logSecurityEvent("Suspicious activity detected", [
                'ip' => $ip,
                'action' => $action,
                'attempts' => $attempts
            ]);
            
            return true;
        }
        
        Cache::put($key, $attempts + 1, 3600); // 1 hour
        
        return false;
    }

    /**
     * Encrypt sensitive data
     */
    public function encryptSensitiveData(string $data): string
    {
        return encrypt($data);
    }

    /**
     * Decrypt sensitive data
     */
    public function decryptSensitiveData(string $encryptedData): string
    {
        return decrypt($encryptedData);
    }

    /**
     * Generate secure file name
     */
    public function generateSecureFileName(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $secureName = Str::random(32) . '.' . $extension;
        
        return $secureName;
    }

    /**
     * Validate API request signature
     */
    public function validateApiSignature(string $signature, string $payload, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get security recommendations
     */
    public function getSecurityRecommendations(): array
    {
        return [
            'enable_2fa' => 'Enable two-factor authentication for all admin accounts',
            'regular_backups' => 'Set up automated daily backups',
            'ssl_certificate' => 'Ensure SSL certificate is properly configured',
            'firewall_rules' => 'Configure firewall to restrict unnecessary ports',
            'monitor_logs' => 'Set up log monitoring and alerting',
            'update_dependencies' => 'Keep all dependencies updated',
            'access_control' => 'Review and audit user access permissions regularly',
            'data_encryption' => 'Encrypt sensitive data at rest',
        ];
    }

    /**
     * Perform security audit
     */
    public function performSecurityAudit(): array
    {
        $audit = [
            'timestamp' => now()->toISOString(),
            'checks' => []
        ];
        
        // Check for weak passwords
        $audit['checks']['weak_passwords'] = $this->checkWeakPasswords();
        
        // Check for inactive users
        $audit['checks']['inactive_users'] = $this->checkInactiveUsers();
        
        // Check for failed login attempts
        $audit['checks']['failed_logins'] = $this->checkFailedLogins();
        
        // Check for suspicious IPs
        $audit['checks']['suspicious_ips'] = $this->checkSuspiciousIPs();
        
        return $audit;
    }

    /**
     * Check for weak passwords
     */
    private function checkWeakPasswords(): array
    {
        // This would check for users with weak passwords
        return ['status' => 'ok', 'count' => 0];
    }

    /**
     * Check for inactive users
     */
    private function checkInactiveUsers(): array
    {
        // This would check for users who haven't logged in for a long time
        return ['status' => 'ok', 'count' => 0];
    }

    /**
     * Check for failed login attempts
     */
    private function checkFailedLogins(): array
    {
        // This would check for recent failed login attempts
        return ['status' => 'ok', 'count' => 0];
    }

    /**
     * Check for suspicious IPs
     */
    private function checkSuspiciousIPs(): array
    {
        // This would check for IPs with suspicious activity
        return ['status' => 'ok', 'count' => 0];
    }
}
