<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Session\Session;

class RecentAccountSwitcher
{
    public const SESSION_KEY = 'recent_account_ids';

    public static function ids(Session $session): array
    {
        return collect((array) $session->get(self::SESSION_KEY, []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->take(5)
            ->values()
            ->all();
    }

    public static function remember(Session $session, ?User ...$users): array
    {
        $ids = self::ids($session);

        foreach ($users as $user) {
            $userId = (int) ($user?->id ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $ids = array_values(array_filter($ids, fn ($existingId) => $existingId !== $userId));
            array_unshift($ids, $userId);
        }

        $ids = array_slice(array_values(array_unique($ids)), 0, 5);
        $session->put(self::SESSION_KEY, $ids);

        return $ids;
    }

    public static function contains(Session $session, int $userId): bool
    {
        return in_array($userId, self::ids($session), true);
    }
}
