<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Cookie;

class RecentAccountSwitcher
{
    public const SESSION_KEY = 'recent_account_ids';
    public const COOKIE_KEY = 'recent_account_ids';
    public const MAX_ACCOUNTS = 10;
    public const COOKIE_LIFETIME_MINUTES = 43200;

    public static function ids(Session $session): array
    {
        return self::normalizeIds($session->get(self::SESSION_KEY, []));
    }

    public static function remember(Session $session, ?User ...$users): array
    {
        $ids = self::mergeUsers(self::ids($session), $users);
        $session->put(self::SESSION_KEY, $ids);

        return $ids;
    }

    public static function idsForRequest(Request $request): array
    {
        $sessionIds = $request->hasSession() ? self::ids($request->session()) : [];
        $ids = self::normalizeIds(array_merge($sessionIds, self::cookieIds($request)));
        $currentUserId = (int) ($request->user()?->id ?? 0);

        if ($currentUserId > 0 && $ids !== [] && ! in_array($currentUserId, $ids, true)) {
            $ids = [$currentUserId];
        }

        if ($request->hasSession() && $sessionIds !== $ids) {
            $request->session()->put(self::SESSION_KEY, $ids);
        }

        return $ids;
    }

    public static function rememberForRequest(Request $request, ?User ...$users): array
    {
        $ids = self::mergeUsers(self::idsForRequest($request), $users);

        if ($request->hasSession()) {
            $request->session()->put(self::SESSION_KEY, $ids);
        }

        self::queueCookie($request, $ids);

        return $ids;
    }

    public static function contains(Session $session, int $userId): bool
    {
        return in_array($userId, self::ids($session), true);
    }

    public static function containsForRequest(Request $request, int $userId): bool
    {
        return in_array($userId, self::idsForRequest($request), true);
    }

    public static function forgetForRequest(Request $request, int $userId): array
    {
        $ids = array_values(array_filter(
            self::idsForRequest($request),
            fn ($existingId) => (int) $existingId !== $userId
        ));

        if ($request->hasSession()) {
            $request->session()->put(self::SESSION_KEY, $ids);
        }

        self::queueCookie($request, $ids);

        return $ids;
    }

    private static function cookieIds(Request $request): array
    {
        $rawCookie = $request->cookie(self::COOKIE_KEY);

        if (is_array($rawCookie)) {
            return self::normalizeIds($rawCookie);
        }

        if (! is_string($rawCookie) || trim($rawCookie) === '') {
            return [];
        }

        $decoded = json_decode($rawCookie, true);

        return is_array($decoded) ? self::normalizeIds($decoded) : [];
    }

    private static function mergeUsers(array $ids, array $users): array
    {
        foreach ($users as $user) {
            $userId = (int) ($user?->id ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $ids = array_values(array_filter($ids, fn ($existingId) => $existingId !== $userId));
            array_unshift($ids, $userId);
        }

        return self::normalizeIds($ids);
    }

    private static function normalizeIds($ids): array
    {
        return collect((array) $ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->take(self::MAX_ACCOUNTS)
            ->values()
            ->all();
    }

    private static function queueCookie(Request $request, array $ids): void
    {
        $secure = config('session.secure');
        $isSecure = is_null($secure) ? $request->isSecure() : (bool) $secure;

        Cookie::queue(
            self::COOKIE_KEY,
            json_encode($ids, JSON_THROW_ON_ERROR),
            self::COOKIE_LIFETIME_MINUTES,
            config('session.path', '/'),
            config('session.domain'),
            $isSecure,
            (bool) config('session.http_only', true),
            false,
            config('session.same_site', 'lax')
        );
    }
}
