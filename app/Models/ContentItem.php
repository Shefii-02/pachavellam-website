<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A banner, what's new, announcement, article or notification shown for the exam types ticked on it. */
class ContentItem extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        // random public id: the player address (/player/{uid}) never shows the YouTube link
        static::creating(function ($m) {
            $m->uid = $m->uid ?: static::makeUid();
        });
    }

    public function playerUrl(): string
    {
        return url('player/' . $this->uid);
    }

    /** The public address of a free class: a random 10 character id (letters and numbers), never the database number. */
    public function publicUrl(): string
    {
        return url('free-classes/' . $this->uid);
    }

    /** 10 random lower-case letters and digits, with at least one letter and one digit; unique across items and special videos. */
    public static function makeUid(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        do {
            $u = '';
            for ($i = 0; $i < 10; $i++) {
                $u .= $chars[random_int(0, 35)];
            }
        } while (!preg_match('/[a-z]/', $u) || !preg_match('/[0-9]/', $u)
            || \DB::table('content_items')->where('uid', $u)->exists() || \DB::table('special_videos')->where('uid', $u)->exists());

        return $u;
    }

    protected $guarded = ['id'];

    protected $casts = ['status' => 'boolean', 'new_tab' => 'boolean', 'is_pinned' => 'boolean', 'scheduled_at' => 'datetime', 'starts_at' => 'date', 'ends_at' => 'date', 'published_at' => 'date', 'last_date' => 'date'];

    /** url segment => [kind, label, icon, form fields] */
    public const KINDS = [
        'free-classes' => ['kind' => 'promo_class', 'label' => 'Free promo classes', 'one' => 'promo class', 'icon' => 'bi-play-btn', 'folders' => true, 'fields' => ['title', 'link_url', 'mode', 'chat_mode', 'scheduled_at', 'folder_id', 'on_home', 'image', 'description']],
        'articles' => ['kind' => 'article', 'label' => 'Free articles', 'one' => 'article', 'icon' => 'bi-newspaper', 'fields' => ['title', 'image', 'description', 'published_at', 'link_url']],
        'banners' => ['kind' => 'banner', 'label' => 'Banners', 'one' => 'banner', 'icon' => 'bi-card-image', 'fields' => ['image', 'title', 'link_url', 'starts_at', 'ends_at']],
        'whats-new' => ['kind' => 'whats_new', 'label' => "What's New", 'one' => "what's new item", 'icon' => 'bi-plus-circle', 'fields' => ['title', 'description', 'link_url', 'image', 'bg_color', 'text_color', 'new_tab']],
        'announcements' => ['kind' => 'announcement', 'label' => 'Announcements', 'one' => 'announcement', 'icon' => 'bi-megaphone', 'fields' => ['title', 'type', 'image', 'description', 'published_at', 'link_url', 'attachment', 'is_pinned']],
        'syllabus' => ['kind' => 'syllabus', 'label' => 'Syllabus', 'one' => 'syllabus', 'icon' => 'bi-plus-slash-minus', 'folders' => true, 'fields' => ['title', 'description', 'published_at', 'link_url', 'attachment', 'folder_id']],
        'materials' => ['kind' => 'material', 'label' => 'Study Materials', 'one' => 'study material', 'icon' => 'bi-file-earmark-arrow-down-fill', 'folders' => true, 'fields' => ['title', 'description', 'published_at', 'link_url', 'attachment', 'folder_id']],
        'results' => ['kind' => 'result', 'label' => 'Results', 'one' => 'result', 'icon' => 'bi-file-earmark-spreadsheet', 'folders' => true, 'fields' => ['title', 'description', 'published_at', 'link_url', 'attachment', 'folder_id']],
        // website content that is not tied to an exam type
        'achievements' => ['kind' => 'achievement', 'label' => 'Achievements', 'one' => 'achievement', 'icon' => 'bi-trophy', 'global' => true, 'fields' => ['title', 'image', 'description', 'link_url']],
        'site-stats' => ['kind' => 'stat', 'label' => 'Website numbers', 'one' => 'number', 'icon' => 'bi-graph-up-arrow', 'global' => true, 'fields' => ['title', 'description']],
        'team' => ['kind' => 'team_member', 'label' => 'Team', 'one' => 'team member', 'icon' => 'bi-person-badge', 'global' => true, 'fields' => ['title', 'type', 'description', 'image']],
        'faqs' => ['kind' => 'faq', 'label' => 'FAQs', 'one' => 'question', 'icon' => 'bi-question-circle', 'global' => true, 'fields' => ['title', 'type', 'description']],
        'key-points' => ['kind' => 'key_point', 'label' => 'Key points', 'one' => 'key point', 'icon' => 'bi-stars', 'global' => true, 'fields' => ['title', 'description', 'image']],
        'partners' => ['kind' => 'partner', 'label' => 'Associate partners', 'one' => 'partner', 'icon' => 'bi-buildings', 'global' => true, 'fields' => ['title', 'image', 'link_url']],
        'founder' => ['kind' => 'founder', 'label' => 'Founder note', 'one' => 'founder note', 'icon' => 'bi-chat-quote', 'global' => true, 'fields' => ['title', 'description', 'image']],
        'footer-links' => ['kind' => 'footer_link', 'label' => 'Footer links', 'one' => 'footer link', 'icon' => 'bi-link-45deg', 'global' => true, 'fields' => ['title', 'type', 'link_url']],
        'notifications' => ['kind' => 'notification', 'label' => 'Notifications', 'one' => 'notification', 'icon' => 'bi-bell', 'fields' => ['title', 'image', 'description', 'published_at', 'last_date', 'link_url', 'attachment']],
    ];

    /** FAQ groups: the tabs of the FAQ block on the home page and the Prime page. */
    public const FAQ_GROUPS = ['general' => 'General', 'services' => 'Our services', 'courses' => 'Course purchase', 'payments' => 'Payments & refunds', 'terms' => 'Terms & policies'];

    /** Where a tap on this item lands (web url + app deep link). */
    public function landing(): array
    {
        return \App\Support\Landing::resolve($this->target_type, $this->target_value, $this->link_url);
    }

    public function getLandingUrlAttribute(): ?string
    {
        return $this->landing()['url'];
    }

    public function folder()
    {
        return $this->belongsTo(ContentFolder::class, 'folder_id');
    }

    public function subject()
    {
        return $this->belongsTo(ExamSubject::class, 'subject_id');
    }

    public function exams()
    {
        return $this->belongsToMany(CompetitiveExam::class, 'content_item_exams', 'item_id', 'exam_id')->withTimestamps();
    }

    public function scopeOfKind($q, string $kind)
    {
        return $q->where('content_items.kind', $kind);
    }

    /** Active items; with $exam (slug or id) only those shown for that exam type. */
    public function scopeLive($q, $exam = null)
    {
        $q->where('content_items.status', 1);
        if ($exam !== null) {
            $q->forExam($exam);
        }

        return $q;
    }

    public function scopeForExam($q, $exam)
    {
        return $q->whereIn('content_items.id', fn ($s) => $s->select('ce.item_id')->from('content_item_exams as ce')
            ->join('competitive_exams as e', 'e.id', '=', 'ce.exam_id')
            ->where(fn ($w) => ctype_digit((string) $exam) ? $w->where('e.id', $exam) : $w->where('e.slug', $exam)));
    }

    /** The description as safe HTML for {!! !!}: tags from the editor are kept, scripts/handlers/js links are removed; plain text keeps its line breaks. */
    public function descriptionHtml(): string
    {
        $t = (string) $this->description;
        if ($t === strip_tags($t)) {
            return nl2br(e($t));
        }
        $t = strip_tags($t, '<p><br><b><strong><i><em><u><s><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><blockquote><table><thead><tbody><tr><th><td><span><div><hr><pre><code><figure><figcaption><sub><sup>');
        $t = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $t);
        $t = preg_replace('/(href|src)\s*=\s*(["\'])\s*(javascript|data):[^"\']*\2/i', '$1=$2#$2', $t);

        return $t;
    }

    /** Is the class chat open right now? off = never, always = any time, live = from 10 min before the live time until 3 h after it. */
    public function chatOpen(): bool
    {
        if ($this->chat_mode === 'always') {
            return true;
        }
        if ($this->chat_mode !== 'live' || $this->mode !== 'live' || !$this->scheduled_at) {
            return false;
        }

        return now()->between($this->scheduled_at->copy()->subMinutes(10), $this->scheduled_at->copy()->addHours(3));
    }

    public function excerpt(int $len = 110): string
    {
        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $this->description)))), $len);
    }

    /**
     * Free classes marked "show on home" (the old Special Videos), shaped like the old rows
     * (title, redirection = YouTube id, category = old KPSC subject id) for the KPSC pages that still list them.
     * $subjectId limits it to the folder made from that KPSC subject.
     */
    public static function homeVideos(?int $subjectId = null)
    {
        $folderIds = $subjectId ? ContentFolder::where('legacy_ref', $subjectId)->pluck('id')->all() : null;

        return static::ofKind('promo_class')->live()->where('on_home', true)
            ->when($folderIds !== null, fn ($q) => $q->whereIn('folder_id', $folderIds ?: [0]))
            ->orderBy('position')->get()->map(function ($c) {
                $legacy = $c->folder_id ? ContentFolder::where('id', $c->folder_id)->value('legacy_ref') : null;

                return (object) ['title' => $c->title, 'redirection' => \App\Services\ClassSource::youtubeId((string) $c->link_url), 'uid' => $c->uid,
                    'category' => $legacy ?: 0, 'url' => $c->publicUrl()];
            })->filter(fn ($v) => $v->redirection)->values();
    }
}
