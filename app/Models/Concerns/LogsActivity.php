<?php

namespace App\Models\Concerns;

use App\Services\ActivityLogger;

/**
 * Add `use LogsActivity;` to a model and what staff create, change, delete and restore on it lands in the activity log.
 * Optional on the model:  protected array $activityIgnore = ['field'];   public function activityNote(string $action): ?string
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn ($m) => ActivityLogger::record('created', $m));

        static::updated(function ($m) {
            $changes = ActivityLogger::diff($m);
            foreach ($m->activityIgnore ?? [] as $f) {
                unset($changes[$f]);
            }
            if ($changes) {
                ActivityLogger::record('updated', $m, null, $changes);
            }
        });

        static::deleted(function ($m) {
            $soft = method_exists($m, 'isForceDeleting') && !$m->isForceDeleting();
            if ($soft && ($m->tracksDeleter ?? false) && ($actor = ActivityLogger::actor())) {
                $m->newQueryWithoutScopes()->whereKey($m->getKey())->update(['deleted_by' => $actor->id]);
            }
            ActivityLogger::record('deleted', $m, $soft ? 'Moved to Trash' : 'Deleted for good');
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($m) {
                if ($m->tracksDeleter ?? false) {
                    $m->newQueryWithoutScopes()->whereKey($m->getKey())->update(['deleted_by' => null]);
                }
                ActivityLogger::record('restored', $m, 'Restored from Trash');
            });
        }
    }
}
