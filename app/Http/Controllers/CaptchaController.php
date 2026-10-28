<?php

namespace App\Http\Controllers;

use App\Services\Captcha;
use Illuminate\Http\Request;

/** GET captcha (new picture), GET captcha/status?form=otp (does this form need one right now?). Same under /api. */
class CaptchaController extends Controller
{
    public function show()
    {
        return response()->json(Captcha::challenge())->header('Cache-Control', 'no-store');
    }

    public function status(Request $request)
    {
        $form = (string) $request->query('form');
        $need = Captcha::required($form, $request);

        return response()->json(['required' => $need] + ($need ? ['captcha' => Captcha::challenge()] : []))->header('Cache-Control', 'no-store');
    }
}
