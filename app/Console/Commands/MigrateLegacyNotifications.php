<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;
use App\Models\User;

class MigrateLegacyNotifications extends Command
{
    protected $signature = 'notifications:migrate-legacy {--delete-original : Delete the legacy (global) notifications after migrating}';

    protected $description = 'Clone legacy global notifications (user_id = NULL) to per-admin Activity rows';

    public function handle(): int
    {
        $deleteOriginal = (bool) $this->option('delete-original');

        $adminIds = User::where('user_type', User::TYPE_ADMIN)->pluck('id');
        if ($adminIds->isEmpty()) {
            $this->warn('No admin users found. Nothing to migrate.');
            return self::SUCCESS;
        }

        $legacy = Activity::whereNull('user_id')->orderBy('id')->get();
        if ($legacy->isEmpty()) {
            $this->info('No legacy notifications found.');
            return self::SUCCESS;
        }

        $createdCount = 0;
        foreach ($legacy as $activity) {
            foreach ($adminIds as $adminId) {
                $exists = Activity::where('user_id', $adminId)
                    ->where('type', $activity->type)
                    ->where('related_id', $activity->related_id)
                    ->where('related_type', $activity->related_type)
                    ->where('description', $activity->description)
                    ->where('properties->migrated_from', $activity->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $props = $activity->properties ?: [];
                if (!is_array($props)) {
                    // Ensure array if cast not applied for some reason
                    $props = json_decode(json_encode($props), true) ?: [];
                }
                $props['migrated_from'] = $activity->id;

                $copy = new Activity([
                    'user_id'      => $adminId,
                    'is_read'      => false,
                    'title'        => $activity->title,
                    'description'  => $activity->description,
                    'message'      => $activity->message,
                    'link'         => $activity->link,
                    'type'         => $activity->type,
                    'related_id'   => $activity->related_id,
                    'related_type' => $activity->related_type,
                    'properties'   => $props,
                    'causer_id'    => $activity->causer_id,
                    'causer_type'  => $activity->causer_type,
                    'subject_id'   => $activity->subject_id,
                    'subject_type' => $activity->subject_type,
                ]);

                // Preserve timestamps
                $copy->created_at = $activity->created_at;
                $copy->updated_at = $activity->updated_at;
                $copy->save();
                $createdCount++;
            }

            if ($deleteOriginal) {
                $activity->delete();
            } else {
                // Mark legacy as read and flag migrated
                $props = $activity->properties ?: [];
                if (!is_array($props)) {
                    $props = json_decode(json_encode($props), true) ?: [];
                }
                $props['migrated'] = true;
                $activity->is_read = true;
                $activity->properties = $props;
                $activity->save();
            }
        }

        $this->info("Created $createdCount per-admin notification(s).");
        if ($deleteOriginal) {
            $this->info('Legacy notifications deleted.');
        } else {
            $this->info('Legacy notifications marked as read and flagged as migrated.');
        }

        return self::SUCCESS;
    }
}

