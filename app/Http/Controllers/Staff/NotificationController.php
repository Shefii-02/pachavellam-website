<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\StaffNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** The staff bell: admin/notifications (full list) and admin/notifications/feed (small JSON the bell refreshes from). */
class NotificationController extends Controller
{
    public function feed(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'unread' => StaffNotifier::unreadCount($user),
            'items' => StaffNotifier::latest($user, 8)->map(fn ($n) => [
                'id' => $n->id, 'type' => $n->type, 'title' => $n->title, 'body' => $n->body, 'url' => $n->url, 'icon' => $n->icon, 'unread' => $n->unread,
                'color' => (\App\Models\StaffNotification::STYLE[$n->type][1] ?? 'secondary'), 'when' => $n->created_at->diffForHumans(),
            ])->values(),
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $read = DB::table('staff_notification_reads')->where('user_id', $user->id)->pluck('notification_id')->flip();

        $list = StaffNotifier::visibleTo($user)
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->input('state') === 'unread', fn ($q) => $q->whereNotIn('id', $read->keys()->all() ?: [0]))
            ->with('exam:id,short_name,name')->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin.staff.notifications', ['list' => $list, 'read' => $read, 'unread' => StaffNotifier::unreadCount($user)]);
    }

    /** POST admin/notifications/read  {ids[]?}  - without ids everything visible is marked read. */
    public function read(Request $request)
    {
        $ids = $request->filled('ids') ? array_map('intval', (array) $request->ids) : null;
        StaffNotifier::markRead($request->user(), $ids);

        return $request->expectsJson() ? response()->json(['unread' => StaffNotifier::unreadCount($request->user())]) : back();
    }

    /** Opening a notification marks it read and goes to where it points. */
    public function open(Request $request, $id)
    {
        $n = StaffNotifier::visibleTo($request->user())->findOrFail($id);
        StaffNotifier::markRead($request->user(), [$n->id]);

        return redirect($n->url ?: url('admin/notifications'));
    }
}
