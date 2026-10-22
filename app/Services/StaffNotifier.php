<?php

namespace App\Services;

use App\Models\StaffNotification;
use App\Models\User;
use App\Support\StaffAccess;
use Illuminate\Support\Facades\DB;

/**
 * The staff bell. Events are written once; each person sees the ones their permissions and exam types allow, and has their own read marks.
 */
class StaffNotifier
{
    public static function push(string $type, string $title, ?string $body = null, ?string $url = null, ?int $examId = null, ?int $subjectId = null): ?StaffNotification
    {
        try {
            [$icon] = StaffNotification::STYLE[$type] ?? ['bi-bell'];

            return StaffNotification::create(['type' => $type, 'title' => mb_substr($title, 0, 190), 'body' => $body ? mb_substr($body, 0, 490) : null, 'url' => $url, 'icon' => $icon, 'exam_id' => $examId, 'subject_id' => $subjectId]);
        } catch (\Throwable $e) {
            report($e);   // a notification must never break the thing that caused it

            return null;
        }
    }

    /** Notifications this person may see (recent ones only). */
    public static function visibleTo(User $user)
    {
        $q = StaffNotification::where('created_at', '>=', now()->subDays((int) config('staff_access.notification_days', 30)));

        if (!StaffAccess::isAdmin($user)) {
            $types = collect(StaffNotification::PERMISSION)->filter(fn ($perm) => $user->can($perm))->keys()->all();
            $q->whereIn('type', $types ?: ['-']);
            $exams = StaffAccess::examIds($user);
            $q->where(fn ($w) => $w->whereNull('exam_id')->orWhereIn('exam_id', $exams ?: [0]));
        }

        return $q;
    }

    public static function unreadCount(User $user): int
    {
        return self::visibleTo($user)->whereNotExists(fn ($s) => $s->select(DB::raw(1))->from('staff_notification_reads as r')
            ->whereColumn('r.notification_id', 'staff_notifications.id')->where('r.user_id', $user->id))->count();
    }

    /** Latest first, with an "unread" flag. */
    public static function latest(User $user, int $limit = 8)
    {
        $read = DB::table('staff_notification_reads')->where('user_id', $user->id)->pluck('notification_id')->flip();

        return self::visibleTo($user)->orderByDesc('id')->limit($limit)->get()->each(fn ($n) => $n->unread = !isset($read[$n->id]));
    }

    public static function markRead(User $user, ?array $ids = null): void
    {
        $q = self::visibleTo($user)->whereNotExists(fn ($s) => $s->select(DB::raw(1))->from('staff_notification_reads as r')
            ->whereColumn('r.notification_id', 'staff_notifications.id')->where('r.user_id', $user->id));
        if ($ids !== null) {
            $q->whereIn('staff_notifications.id', $ids);
        }
        foreach ($q->pluck('staff_notifications.id') as $id) {
            DB::table('staff_notification_reads')->insertOrIgnore(['user_id' => $user->id, 'notification_id' => $id, 'read_at' => now()]);
        }
    }
}
