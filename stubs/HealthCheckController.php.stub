<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HealthCheckController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('health-check', [
            'checks' => $this->runChecks(),
        ]);
    }

    /**
     * @return array<string, array{status: string, message: string, latency_ms: float|null}>
     */
    private function runChecks(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'redis' => $this->checkRedis(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
        ];

        if (class_exists(\Laravel\Reverb\ReverbServiceProvider::class)) {
            $checks['reverb'] = $this->checkReverb();
        }

        if (class_exists(\Laravel\Horizon\Horizon::class)) {
            $checks['horizon'] = $this->checkHorizon();
        }

        return $checks;
    }

    /**
     * @return array{status: string, message: string, latency_ms: float|null}
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::select('SELECT 1');
            $coldLatency = (microtime(true) - $start) * 1000;

            $start2 = microtime(true);
            DB::select('SELECT 1');
            $warmLatency = (microtime(true) - $start2) * 1000;

            $host = config('database.connections.'.config('database.default').'.host');

            return [
                'status' => 'ok',
                'message' => sprintf(
                    'Database OK (%s @ %s) - cold: %.1fms, warm: %.1fms',
                    config('database.default'),
                    $host,
                    $coldLatency,
                    $warmLatency
                ),
                'latency_ms' => round($warmLatency, 2),
            ];
        } catch (\Throwable $throwable) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: '.$throwable->getMessage(),
                'latency_ms' => null,
            ];
        }
    }

    /**
     * @return array{status: string, message: string, latency_ms: float|null}
     */
    private function checkCache(): array
    {
        $start = microtime(true);
        $key = 'health_check_'.uniqid();

        try {
            Cache::put($key, 'test_value', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            $latency = (microtime(true) - $start) * 1000;

            if ($value === 'test_value') {
                return [
                    'status' => 'ok',
                    'message' => 'Cache read/write successful ('.config('cache.default').')',
                    'latency_ms' => round($latency, 2),
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Cache value mismatch',
                'latency_ms' => round($latency, 2),
            ];
        } catch (\Throwable $throwable) {
            return [
                'status' => 'error',
                'message' => 'Cache failed: '.$throwable->getMessage(),
                'latency_ms' => null,
            ];
        }
    }

    /**
     * @return array{status: string, message: string, latency_ms: float|null}
     */
    private function checkRedis(): array
    {
        $start = microtime(true);

        try {
            Redis::ping();
            $latency = (microtime(true) - $start) * 1000;

            return [
                'status' => 'ok',
                'message' => 'Redis PING successful',
                'latency_ms' => round($latency, 2),
            ];
        } catch (\Throwable $throwable) {
            return [
                'status' => 'error',
                'message' => 'Redis failed: '.$throwable->getMessage(),
                'latency_ms' => null,
            ];
        }
    }

    /**
     * @return array{status: string, message: string, latency_ms: float|null}
     */
    private function checkReverb(): array
    {
        $start = microtime(true);

        try {
            $host = config('reverb.servers.reverb.host', 'localhost');
            $port = config('reverb.servers.reverb.port', 8080);

            Http::timeout(5)->get("http://{$host}:{$port}");

            $latency = (microtime(true) - $start) * 1000;

            return [
                'status' => 'ok',
                'message' => "Reverb server responding at {$host}:{$port}",
                'latency_ms' => round($latency, 2),
            ];
        } catch (\Throwable $throwable) {
            return [
                'status' => 'error',
                'message' => 'Reverb connection failed: '.$throwable->getMessage(),
                'latency_ms' => null,
            ];
        }
    }

    /**
     * @return array{status: string, message: string, latency_ms: float|null}
     */
    private function checkQueue(): array
    {
        $start = microtime(true);

        try {
            $connection = config('queue.default');
            $size = Queue::size();
            $latency = (microtime(true) - $start) * 1000;

            return [
                'status' => 'ok',
                'message' => "Queue accessible ({$connection}), {$size} jobs pending",
                'latency_ms' => round($latency, 2),
            ];
        } catch (\Throwable $throwable) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed: '.$throwable->getMessage(),
                'latency_ms' => null,
            ];
        }
    }

    /**
     * @return array{status: string, message: string, latency_ms: float|null}
     */
    private function checkStorage(): array
    {
        $start = microtime(true);
        $testFile = 'health_check_'.uniqid().'.txt';

        try {
            Storage::put($testFile, 'health check');
            $exists = Storage::exists($testFile);
            Storage::delete($testFile);

            $latency = (microtime(true) - $start) * 1000;

            if ($exists) {
                return [
                    'status' => 'ok',
                    'message' => 'Storage read/write successful ('.config('filesystems.default').')',
                    'latency_ms' => round($latency, 2),
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Storage file not found after write',
                'latency_ms' => round($latency, 2),
            ];
        } catch (\Throwable $throwable) {
            return [
                'status' => 'error',
                'message' => 'Storage failed: '.$throwable->getMessage(),
                'latency_ms' => null,
            ];
        }
    }

    /**
     * @return array{status: string, message: string, latency_ms: float|null}
     */
    private function checkHorizon(): array
    {
        $start = microtime(true);

        try {
            $masters = app(\Laravel\Horizon\Contracts\MasterSupervisorRepository::class)->all();
            $latency = (microtime(true) - $start) * 1000;

            if (count($masters) > 0) {
                return [
                    'status' => 'ok',
                    'message' => 'Horizon is running ('.count($masters).' master supervisor(s))',
                    'latency_ms' => round($latency, 2),
                ];
            }

            return [
                'status' => 'warning',
                'message' => 'Horizon: no master supervisors found',
                'latency_ms' => round($latency, 2),
            ];
        } catch (\Throwable $throwable) {
            return [
                'status' => 'error',
                'message' => 'Horizon check failed: '.$throwable->getMessage(),
                'latency_ms' => null,
            ];
        }
    }
}
