<?php

namespace App\Http\Middleware;

use App\Models\UserPlatformStat;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackUserPlatform
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user() ?: Auth::user();
        if (!$user) {
            return $response;
        }

        try {
            $platform = $this->resolvePlatform($request);

            // Throttle write frequency to reduce DB churn on highly interactive pages.
            $touchKey = 'platform_touch:' . $user->id . ':' . $platform;
            if (Cache::has($touchKey)) {
                return $response;
            }
            Cache::put($touchKey, true, now()->addSeconds(45));

            $stat = UserPlatformStat::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'last_platform' => $platform,
                    'web_hits' => 0,
                    'app_hits' => 0,
                ]
            );

            $counter = $platform === UserPlatformStat::PLATFORM_APP ? 'app_hits' : 'web_hits';
            $stat->forceFill([
                'last_platform' => $platform,
                'last_seen_at' => now(),
                'last_ip' => $request->ip(),
                'last_user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ])->save();
            $stat->increment($counter);
        } catch (\Throwable $e) {
            // Silent fail to avoid impacting user traffic.
        }

        return $response;
    }

    private function resolvePlatform(Request $request): string
    {
        $headerHint = strtolower(trim((string) ($request->header('X-Client-Platform') ?: $request->header('X-Platform'))));

        if (in_array($headerHint, ['app', 'mobile', 'ios', 'android', 'native'], true)) {
            return UserPlatformStat::PLATFORM_APP;
        }

        if (in_array($headerHint, ['web', 'website', 'browser'], true)) {
            return UserPlatformStat::PLATFORM_WEB;
        }

        if ($request->is('api/*')) {
            return UserPlatformStat::PLATFORM_APP;
        }

        $userAgent = strtolower((string) $request->userAgent());
        $appSignatures = ['okhttp', 'dart', 'flutter', 'reactnative', 'postmanruntime', 'insomnia'];
        foreach ($appSignatures as $signature) {
            if (str_contains($userAgent, $signature)) {
                return UserPlatformStat::PLATFORM_APP;
            }
        }

        return UserPlatformStat::PLATFORM_WEB;
    }
}
