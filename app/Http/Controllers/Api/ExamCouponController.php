<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamCourse;
use App\Services\CouponService;
use Illuminate\Http\Request;

/**
 * POST api/exam-coupon-check   { "code": "ONAM25", "course_id": 12 }   (logged in student)
 * Tells the app whether the coupon works for this student and course, and what the student would pay.
 * It never uses the coupon up: redeeming happens at payment time (CouponService::redeem).
 */
class ExamCouponController extends Controller
{
    public function check(Request $request, CouponService $coupons)
    {
        $data = $request->validate([
            'code' => 'required|string|max:60',
            'course_id' => 'required|integer',
        ]);

        $course = ExamCourse::where('status', 'published')->find($data['course_id']);
        if (!$course) {
            return response()->json(['valid' => false, 'message' => 'Course not found.', 'status' => 404], 404);
        }

        $r = $coupons->check($data['code'], $request->user(), $course);

        return response()->json([
            'valid' => $r['ok'],
            'message' => $r['message'],
            'code' => $r['ok'] ? $r['coupon']->code : null,
            'discount_label' => $r['ok'] ? $r['coupon']->discountLabel() : null,
            'original_amount' => $r['original'],
            'discount_amount' => $r['discount'],
            'final_amount' => $r['final'],
            'status' => 200,
        ]);
    }
}
