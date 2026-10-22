<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\SpecialVideo;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Video chats (admin/class-chats): the live chat beside each free class / special video, kept in class_chat_messages
 * and separate from the student-staff conversations of admin/chat. One row per video; open it to read, reply, delete messages
 * and switch the chat on or off.
 */
class ClassChatAdminController extends Controller
{
    private function allow(Request $request): void
    {
        StaffAccess::authorize($request, 'Staff chat use');
    }

    /** @return array{0:object,1:string,2:string} [model, title, public player uid] */
    private function video(string $source, $id)
    {
        $m = $source === 'video' ? SpecialVideo::findOrFail($id) : ContentItem::ofKind('promo_class')->findOrFail($id);

        return $m;
    }

    public function index(Request $request)
    {
        $this->allow($request);
        $stats = DB::table('class_chat_messages')->whereNull('deleted_at')
            ->selectRaw('source, item_id, count(*) as total, count(distinct user_id) as people, max(created_at) as last_at, max(id) as last_id')
            ->groupBy('source', 'item_id')->get()->keyBy(fn ($r) => $r->source . ':' . $r->item_id);

        $rows = collect();
        $items = ContentItem::ofKind('promo_class')->with('exams:id,short_name,name')->get(['id', 'title', 'uid', 'mode', 'chat_mode', 'scheduled_at', 'status']);
        foreach ($items as $i) {
            $s = $stats->get('item:' . $i->id);
            if ($i->chat_mode !== 'off' || $s) {
                $rows->push((object) ['source' => 'item', 'id' => $i->id, 'title' => $i->title, 'mode' => $i->chat_mode, 'live' => $i->mode === 'live', 'at' => $i->scheduled_at, 'open' => $i->chatOpen(),
                    'exams' => $i->exams->map(fn ($e) => $e->short_name ?: $e->name)->implode(', '), 'total' => (int) ($s->total ?? 0), 'people' => (int) ($s->people ?? 0), 'last' => $s?->last_at ? Carbon::parse($s->last_at) : null]);
            }
        }
        foreach (SpecialVideo::get(['id', 'title', 'chat_mode']) as $v) {
            $s = $stats->get('video:' . $v->id);
            if (($v->chat_mode ?? 'off') !== 'off' || $s) {
                $rows->push((object) ['source' => 'video', 'id' => $v->id, 'title' => $v->title, 'mode' => $v->chat_mode ?: 'off', 'live' => false, 'at' => null, 'open' => $v->chat_mode === 'always',
                    'exams' => 'Special video', 'total' => (int) ($s->total ?? 0), 'people' => (int) ($s->people ?? 0), 'last' => $s?->last_at ? Carbon::parse($s->last_at) : null]);
            }
        }
        $q = trim((string) $request->query('q'));
        if ($q !== '') {
            $rows = $rows->filter(fn ($r) => stripos($r->title, $q) !== false);
        }
        if ($request->query('state') === 'open') {
            $rows = $rows->where('open', true);
        } elseif ($request->query('state') === 'busy') {
            $rows = $rows->where('total', '>', 0);
        }
        $rows = $rows->sortByDesc(fn ($r) => $r->last ? $r->last->timestamp : 0)->values();

        return view('admin.class-chat.index', ['rows' => $rows, 'q' => $q, 'state' => $request->query('state'), 'totalMessages' => DB::table('class_chat_messages')->whereNull('deleted_at')->count()]);
    }

    public function show(Request $request, string $source, $id)
    {
        $this->allow($request);
        $v = $this->video($source, $id);

        return view('admin.class-chat.show', ['v' => $v, 'source' => $source, 'canPost' => true]);
    }

    public function messages(Request $request, string $source, $id)
    {
        $this->allow($request);
        $this->video($source, $id);
        $after = (int) $request->query('after', 0);
        $q = DB::table('class_chat_messages as m')->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->where('m.source', $source)->where('m.item_id', $id)->whereNull('m.deleted_at')->select('m.id', 'm.user_id', 'm.body', 'm.created_at', 'u.name', 'u.mobile');
        $rows = $after ? $q->where('m.id', '>', $after)->orderBy('m.id')->limit(200)->get() : $q->orderByDesc('m.id')->limit(150)->get()->reverse()->values();
        $deleted = $after ? DB::table('class_chat_messages')->where('source', $source)->where('item_id', $id)->whereNotNull('deleted_at')->where('deleted_at', '>=', now()->subMinutes(2))->pluck('id') : collect();

        return response()->json([
            'me' => $request->user()->id, 'deleted' => $deleted,
            'messages' => $rows->map(fn ($r) => ['id' => $r->id, 'uid' => $r->user_id, 'mine' => (int) $r->user_id === $request->user()->id, 'name' => trim((string) $r->name) ?: 'Student', 'mobile' => $r->mobile,
                'body' => $r->body, 't' => Carbon::parse($r->created_at)->format('d M, h:i a')])->all(),
        ]);
    }

    public function send(Request $request, string $source, $id)
    {
        $this->allow($request);
        $this->video($source, $id);
        $data = $request->validate(['body' => ['required', 'string', 'max:400']]);
        $mid = DB::table('class_chat_messages')->insertGetId(['source' => $source, 'item_id' => $id, 'user_id' => $request->user()->id, 'body' => trim(strip_tags($data['body'])), 'created_at' => now()]);

        return response()->json(['id' => $mid]);
    }

    public function destroy(Request $request, string $source, $id, $message)
    {
        $this->allow($request);
        DB::table('class_chat_messages')->where('source', $source)->where('item_id', $id)->where('id', $message)->update(['deleted_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /** Delete every message of one student in this chat (spam). */
    public function destroyUser(Request $request, string $source, $id, $userId)
    {
        $this->allow($request);
        $n = DB::table('class_chat_messages')->where('source', $source)->where('item_id', $id)->where('user_id', $userId)->whereNull('deleted_at')->update(['deleted_at' => now()]);

        return response()->json(['deleted' => $n]);
    }

    public function mode(Request $request, string $source, $id)
    {
        $this->allow($request);
        $v = $this->video($source, $id);
        $mode = $request->validate(['chat_mode' => ['required', 'in:off,live,always']])['chat_mode'];
        if ($source === 'video' && $mode === 'live') {
            $mode = 'always';   // special videos have no live time
        }
        DB::table($source === 'video' ? 'special_videos' : 'content_items')->where('id', $v->id)->update(['chat_mode' => $mode]);

        return back()->with('message', 'Chat is now ' . ['off' => 'off', 'live' => 'on during the live class only', 'always' => 'on'][$mode]);
    }
}
