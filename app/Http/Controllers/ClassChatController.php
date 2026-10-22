<?php

namespace App\Http\Controllers;

use App\Models\ContentItem;
use App\Models\SpecialVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Chat beside a video in the common player (/player/{uid}/chat): read by anyone, written by signed-in students,
 * only while that video's chat setting allows it. Works for free classes and for the home page's special videos.
 */
class ClassChatController extends Controller
{
    /** @return array{0:string,1:int,2:string,3:bool,4:string}|null [source, id, chat_mode, open now, closed text] */
    private function target(string $uid): ?array
    {
        if ($c = ContentItem::ofKind('promo_class')->live()->where('uid', $uid)->first()) {
            $text = $c->chat_mode === 'live' ? 'Chat is open only during the live class' . ($c->scheduled_at ? ' (' . $c->scheduled_at->format('d M, h:i a') . ').' : '.') : 'Chat is closed.';

            return ['item', $c->id, $c->chat_mode, $c->chatOpen(), $text];
        }
        if ($v = SpecialVideo::where('status', 1)->where('uid', $uid)->first()) {
            return ['video', $v->id, $v->chat_mode ?: 'off', ($v->chat_mode ?? 'off') === 'always', 'Chat is closed.'];
        }

        return null;
    }

    public function messages(Request $request, $uid)
    {
        abort_unless($t = $this->target($uid), 404);
        [$source, $id, $mode, $open, $closedText] = $t;
        if ($mode === 'off') {
            return response()->json(['enabled' => false, 'open' => false, 'messages' => []]);
        }
        $after = (int) $request->query('after', 0);
        $q = DB::table('class_chat_messages as m')->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->where('m.source', $source)->where('m.item_id', $id)->whereNull('m.deleted_at')->select('m.id', 'm.user_id', 'm.body', 'm.created_at', 'u.name');
        $rows = $after
            ? $q->where('m.id', '>', $after)->orderBy('m.id')->limit(100)->get()
            : $q->orderByDesc('m.id')->limit(60)->get()->reverse()->values();   // first load: the latest 60
        $me = optional($request->user())->id;

        return response()->json([
            'enabled' => true, 'open' => $open, 'me' => $me, 'closedText' => $closedText,
            'messages' => $rows->map(fn ($r) => [
                'id' => $r->id, 'mine' => (int) $r->user_id === $me,
                'name' => trim((string) $r->name) ?: 'Student', 'body' => $r->body, 't' => \Illuminate\Support\Carbon::parse($r->created_at)->format('h:i a'),
            ])->all(),
        ]);
    }

    public function send(Request $request, $uid)
    {
        abort_unless($t = $this->target($uid), 404);
        [$source, $id, $mode, $open] = $t;
        if (!$open) {
            return response()->json(['message' => 'Chat is closed for this video.'], 403);
        }
        $data = $request->validate(['body' => ['required', 'string', 'max:400']]);
        $body = trim(strip_tags($data['body']));
        if ($body === '') {
            return response()->json(['message' => 'Write a message first.'], 422);
        }
        $mid = DB::table('class_chat_messages')->insertGetId(['source' => $source, 'item_id' => $id, 'user_id' => $request->user()->id, 'body' => $body, 'created_at' => now()]);

        return response()->json(['id' => $mid]);
    }
}
