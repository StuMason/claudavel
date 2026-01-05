import { Head, router } from '@inertiajs/react';
import { RefreshCw } from 'lucide-react';
import { useState } from 'react';

interface HealthCheck {
    status: 'ok' | 'error' | 'warning';
    message: string;
    latency_ms: number | null;
}

interface Props {
    checks: Record<string, HealthCheck>;
}

export default function HealthCheck({ checks }: Props) {
    const [refreshing, setRefreshing] = useState(false);

    const refresh = () => {
        setRefreshing(true);
        router.reload({
            onFinish: () => setRefreshing(false),
        });
    };

    const statusColor = (status: string) => {
        switch (status) {
            case 'ok':
                return 'bg-green-500';
            case 'warning':
                return 'bg-yellow-500';
            case 'error':
                return 'bg-red-500';
            default:
                return 'bg-gray-500';
        }
    };

    const statusBg = (status: string) => {
        switch (status) {
            case 'ok':
                return 'bg-green-500/10 border-green-500/20';
            case 'warning':
                return 'bg-yellow-500/10 border-yellow-500/20';
            case 'error':
                return 'bg-red-500/10 border-red-500/20';
            default:
                return 'bg-gray-500/10 border-gray-500/20';
        }
    };

    const allOk = Object.values(checks).every(
        (c) => c.status === 'ok' || c.status === 'warning',
    );

    return (
        <>
            <Head title="Health Check" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-zinc-950 p-4">
                <div className="w-full max-w-2xl space-y-6">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <div
                                className={`h-4 w-4 rounded-full ${allOk ? 'bg-green-500' : 'bg-red-500'} animate-pulse`}
                            />
                            <h1 className="text-2xl font-bold text-white">
                                System Health
                            </h1>
                        </div>
                        <button
                            onClick={refresh}
                            disabled={refreshing}
                            className="flex items-center gap-2 rounded-lg bg-zinc-800 px-4 py-2 text-sm text-white transition hover:bg-zinc-700 disabled:opacity-50"
                        >
                            <RefreshCw
                                className={`h-4 w-4 ${refreshing ? 'animate-spin' : ''}`}
                            />
                            Refresh
                        </button>
                    </div>

                    <div className="grid gap-3">
                        {Object.entries(checks).map(([name, check]) => (
                            <div
                                key={name}
                                className={`rounded-xl border p-4 ${statusBg(check.status)}`}
                            >
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div
                                            className={`h-3 w-3 rounded-full ${statusColor(check.status)}`}
                                        />
                                        <span className="font-mono text-sm font-medium text-white uppercase">
                                            {name}
                                        </span>
                                    </div>
                                    {check.latency_ms !== null && (
                                        <span className="font-mono text-xs text-zinc-400">
                                            {check.latency_ms}ms
                                        </span>
                                    )}
                                </div>
                                <p className="mt-2 text-sm text-zinc-300">
                                    {check.message}
                                </p>
                            </div>
                        ))}
                    </div>

                    <div className="text-center text-xs text-zinc-600">
                        Last checked: {new Date().toLocaleString()}
                    </div>
                </div>
            </div>
        </>
    );
}
