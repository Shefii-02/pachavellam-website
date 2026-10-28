<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\PushCampaign;
use App\Support\Landing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Sends a push campaign to every device of every person in the audience
 * (one person can have several devices: Android, iOS, web - all of them get it).
 */
class PushNotifier
{
    public function __construct(private FirebasePushService $firebase)
    {
    }

    /** Create the campaign row; call send() (or dispatch after the response) to deliver it. */
    public static function create(array $data): PushCampaign
    {
        return PushCampaign::create($data + ['status' => 'queued']);
    }

    /** Device token rows for the campaign's audience. */
    public function devices(PushCampaign $c)
    {
        $q = DeviceToken::query()->join('users', 'users.id', '=', 'device_tokens.user_id')->where('users.push_enabled', 1)->select('device_tokens.*');
        if ($c->audience === 'interested') {
            $q->whereIn('device_tokens.user_id', DB::table('student_exam_interests')->select('user_id'));   // interested in any exam type
        } elseif ($c->audience === 'exams' && $c->audience_exam_ids) {
            $q->whereIn('device_tokens.user_id', DB::table('student_exam_interests')->whereIn('exam_id', $c->audience_exam_ids)->select('user_id'));
        }
        if (!empty($c->platforms)) {
            $q->whereIn('device_tokens.platform', $c->platforms);
        }

        return $q->get();
    }

    public function send(PushCampaign $c): PushCampaign
    {
        $devices = $this->devices($c);
        $c->users = $devices->pluck('user_id')->unique()->count();
        $c->devices = $devices->count();

        if ($devices->isEmpty()) {
            $c->status = 'no_devices';
            $c->save();

            return $c;
        }

        $landing = Landing::resolve($c->target_type, $c->target_value, $c->target_type === 'link' ? $c->target_value : null);
        $data = array_filter([
            'type' => 'announcement', 'campaign_id' => $c->id,
            'landing_type' => $landing['type'], 'landing_value' => $landing['value'], 'landing_url' => $landing['url'], 'landing_app' => $landing['app'],
        ], fn ($v) => $v !== null);
        $image = $c->image ? url(Storage::url('files/' . $c->image)) : null;

        $report = $this->firebase->sendRich($devices->pluck('token')->unique()->values()->all(), $c->title, $c->body, $data, $image, $landing['url']);
        if ($report === null) {
            $c->status = 'no_credentials';
            $c->save();

            return $c;
        }
        if ($report['invalid']) {
            DeviceToken::whereIn('token', $report['invalid'])->delete();   // uninstalled / expired devices
        }
        $c->delivered = $report['delivered'];
        $c->failed = $report['failed'];
        $c->status = 'sent';
        $c->save();

        return $c;
    }
}
