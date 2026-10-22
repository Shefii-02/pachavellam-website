<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use App\Services\CouponService;
use App\Services\Payments\AdmissionService;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentLinkService;
use Illuminate\Http\Request;

/**
 * The public payment page: pay/{token}. No login: the secret link is the proof, and it is tied to one student and one course.
 *   GET  pay/{token}          the page
 *   POST pay/{token}/start    makes the order and returns what the page needs to open Razorpay / PhonePe
 *   POST pay/{token}/verify   Razorpay reports the finished payment (checked by signature)
 *   GET  pay/{token}/status   is it paid yet?
 */
class PayLinkPageController extends Controller
{
    private function link(string $token): PaymentLink
    {
        return PaymentLink::with(['course', 'user:id,name,mobile,email'])->where('token', $token)->firstOrFail();
    }

    public function show(string $token, CouponService $coupons)
    {
        $link = $this->link($token);
        $link->increment('opens');
        $link->update(['last_opened_at' => now()]);

        $quote = ['original' => $link->course->effectivePrice(), 'discount' => 0.0, 'final' => $link->course->effectivePrice()];
        if ($link->coupon_code && $link->isUsable()) {
            $r = $coupons->check($link->coupon_code, $link->user, $link->course);
            if ($r['ok']) {
                $quote = ['original' => $r['original'], 'discount' => $r['discount'], 'final' => $r['final']];
            }
        }

        return view('pay.link', ['link' => $link, 'quote' => $quote, 'state' => $link->displayStatus(), 'maskedName' => $this->mask($link->user?->name)]);
    }

    public function start(Request $request, string $token, PaymentLinkService $links)
    {
        $link = $this->link($token);

        try {
            $r = $links->start($link);
        } catch (PaymentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($r + ['name' => $link->user?->name]);
    }

    public function verify(Request $request, string $token, AdmissionService $admissions)
    {
        $link = $this->link($token);
        $data = $request->validate([
            'merchant_ref' => 'required|string|max:40', 'razorpay_order_id' => 'nullable|string', 'razorpay_payment_id' => 'nullable|string', 'razorpay_signature' => 'nullable|string',
        ]);

        // the payment must belong to this link's order
        $tx = PaymentTransaction::where('merchant_ref', $data['merchant_ref'])->where('admission_id', $link->admission_id)->where('type', 'payment')->first();
        if (!$tx) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        try {
            if (!$admissions->verifyReturn($tx, $data)) {
                return response()->json(['message' => 'The payment could not be verified.', 'paid' => false], 422);
            }
        } catch (PaymentException $e) {
            return response()->json(['message' => $e->getMessage(), 'paid' => false], 422);
        }

        return response()->json(['paid' => $tx->fresh()->status === 'success']);
    }

    public function status(string $token, AdmissionService $admissions)
    {
        $link = $this->link($token);
        $tx = $link->admission_id ? PaymentTransaction::where('admission_id', $link->admission_id)->where('type', 'payment')->latest('id')->first() : null;

        if ($tx && in_array($tx->status, ['created', 'pending'], true) && $tx->isOnline()) {
            try {
                $admissions->confirm($tx, app(\App\Services\Payments\PaymentManager::class)->gateway($tx->gateway)->fetchStatus($tx));
            } catch (PaymentException $e) {
                // gateway not reachable: report what we know
            }
        }

        return response()->json(['paid' => $link->fresh()->status === 'paid', 'state' => $link->fresh()->displayStatus()]);
    }

    private function mask(?string $name): string
    {
        $name = trim((string) $name);

        return $name === '' ? 'Student' : mb_substr($name, 0, 1) . str_repeat('•', max(2, min(8, mb_strlen($name) - 1)));
    }
}
