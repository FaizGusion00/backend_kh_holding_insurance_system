<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    /**
     * Health check endpoint for monitoring and testing
     */
    public function check(Request $request)
    {
        $checks = [];
        $overallStatus = 'healthy';
        $timestamp = now()->toISOString();

        // Check database connection
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'response_time_ms' => $this->measureTime(function() {
                    DB::select('SELECT 1');
                })
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'response_time_ms' => null
            ];
            $overallStatus = 'unhealthy';
        }

        // Check cache (if using Redis)
        try {
            if (config('cache.default') === 'redis') {
                Redis::ping();
                $checks['cache'] = [
                    'status' => 'healthy',
                    'message' => 'Redis cache connection successful',
                    'response_time_ms' => $this->measureTime(function() {
                        Cache::put('health_check', 'test', 1);
                        Cache::get('health_check');
                    })
                ];
            } else {
                $checks['cache'] = [
                    'status' => 'healthy',
                    'message' => 'Using ' . config('cache.default') . ' cache driver',
                    'response_time_ms' => null
                ];
            }
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'unhealthy',
                'message' => 'Cache connection failed: ' . $e->getMessage(),
                'response_time_ms' => null
            ];
            $overallStatus = 'unhealthy';
        }

        // Check application status
        $checks['application'] = [
            'status' => 'healthy',
            'message' => 'Application is running',
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'uptime_seconds' => $this->getUptime()
        ];

        // Check JWT configuration
        try {
            $jwtSecret = config('jwt.secret');
            $checks['jwt'] = [
                'status' => !empty($jwtSecret) ? 'healthy' : 'unhealthy',
                'message' => !empty($jwtSecret) ? 'JWT secret configured' : 'JWT secret not configured',
                'configured' => !empty($jwtSecret)
            ];
            if (empty($jwtSecret)) {
                $overallStatus = 'unhealthy';
            }
        } catch (\Exception $e) {
            $checks['jwt'] = [
                'status' => 'unhealthy',
                'message' => 'JWT configuration error: ' . $e->getMessage(),
                'configured' => false
            ];
            $overallStatus = 'unhealthy';
        }

        // Check Curlec payment gateway configuration
        try {
            $curlecKeyId = config('services.curlec.key_id');
            $curlecKeySecret = config('services.curlec.key_secret');
            $checks['payment_gateway'] = [
                'status' => (!empty($curlecKeyId) && !empty($curlecKeySecret)) ? 'healthy' : 'unhealthy',
                'message' => (!empty($curlecKeyId) && !empty($curlecKeySecret)) ? 'Curlec payment gateway configured' : 'Curlec payment gateway not configured',
                'configured' => (!empty($curlecKeyId) && !empty($curlecKeySecret))
            ];
        } catch (\Exception $e) {
            $checks['payment_gateway'] = [
                'status' => 'unhealthy',
                'message' => 'Payment gateway configuration error: ' . $e->getMessage(),
                'configured' => false
            ];
        }

        // Check mail configuration
        try {
            $mailHost = config('mail.host');
            $mailUsername = config('mail.username');
            $checks['mail'] = [
                'status' => (!empty($mailHost) && !empty($mailUsername)) ? 'healthy' : 'unhealthy',
                'message' => (!empty($mailHost) && !empty($mailUsername)) ? 'Mail configuration found' : 'Mail configuration incomplete',
                'configured' => (!empty($mailHost) && !empty($mailUsername))
            ];
        } catch (\Exception $e) {
            $checks['mail'] = [
                'status' => 'unhealthy',
                'message' => 'Mail configuration error: ' . $e->getMessage(),
                'configured' => false
            ];
        }

        $response = [
            'status' => $overallStatus,
            'timestamp' => $timestamp,
            'service' => 'KH Holdings Insurance API',
            'version' => '1.0.0',
            'checks' => $checks,
            'uptime' => $this->getUptime(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_space' => $this->getDiskSpace()
        ];

        $httpStatus = $overallStatus === 'healthy' ? 200 : 503;
        
        return response()->json($response, $httpStatus);
    }

    /**
     * Measure execution time of a function
     */
    private function measureTime(callable $callback)
    {
        $start = microtime(true);
        $callback();
        $end = microtime(true);
        return round(($end - $start) * 1000, 2);
    }

    /**
     * Get application uptime
     */
    private function getUptime()
    {
        try {
            $uptime = shell_exec('uptime -p 2>/dev/null');
            return $uptime ? trim($uptime) : 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        
        return [
            'current_mb' => round($memoryUsage / 1024 / 1024, 2),
            'peak_mb' => round($memoryPeak / 1024 / 1024, 2),
            'limit_mb' => ini_get('memory_limit')
        ];
    }

    /**
     * Get disk space information
     */
    private function getDiskSpace()
    {
        try {
            $bytes = disk_free_space(storage_path());
            $totalBytes = disk_total_space(storage_path());
            
            return [
                'free_gb' => round($bytes / 1024 / 1024 / 1024, 2),
                'total_gb' => round($totalBytes / 1024 / 1024 / 1024, 2),
                'used_percent' => round((($totalBytes - $bytes) / $totalBytes) * 100, 2)
            ];
        } catch (\Exception $e) {
            return [
                'free_gb' => 'Unknown',
                'total_gb' => 'Unknown',
                'used_percent' => 'Unknown'
            ];
        }
    }
}
