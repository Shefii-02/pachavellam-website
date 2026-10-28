<?php

namespace App\Http\Middleware;

use App\Services\Captcha;
use Closure;
use Illuminate\Http\Request;

/** ->middleware('captcha:otp'): when the form needs a captcha (admin/security) the request must carry captcha_id + captcha_answer. */
class RequireCaptcha
{
    public function handle(Request $request, Closure $next, string $form)
    {
        if (Captcha::required($form, $request)) {
            if (!Captcha::verify($request->input('captcha_id'), $request->input('captcha_answer'))) {
                $msg = $request->filled('captcha_answer') ? 'The code you typed is wrong. Try the new one.' : 'Type the code shown in the picture.';

                return $request->expectsJson() || $request->is('api/*')
                    ? response()->json(['code' => 'captcha', 'message' => $msg, 'captcha' => Captcha::challenge()], 422)
                    : back()->withInput()->withErrors(['captcha_answer' => $msg]);
            }
        }

        $response = $next($request);

        // repeated failures from one IP switch the captcha on (mode "after failures")
        if ($response->getStatusCode() >= 400 && $response->getStatusCode() !== 429) {
            Captcha::fail($request);
        }

        return $response;
    }
}
