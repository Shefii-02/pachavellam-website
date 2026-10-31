<?php

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



if (!function_exists('per_page')) {
     /** Page size chosen by the admin (?per_page=), falling back to the listing's own default. */
     function per_page(int $default = 20): int
     {
          $n = (int) request('per_page');
          return in_array($n, [10, 20, 25, 30, 50, 100, 200], true) ? $n : $default;
     }
}

if (!function_exists('admin')) {
     function kpsc_cms($url)
     {
          return url('admin/kpsc/' . $url);
     }
     function kpsc($url)
     {
          return url('kpsc/' . $url);
     }
     function ug_pg_cms($url)
     {
          return url('admin/ug-pg/' . $url);
     }
     function teaching_cms($url)
     {
          return url('admin/teaching/' . $url);
     }
     function admin_general($url)
     {
          return url('admin/general/' . $url);
     }
     function student_cms($url)
     {
          return url('admin/students/' . $url);
     }
}

function ug_pg($url)
{
     return url('ug-pg/' . $url);
}

function remove_slug($value)
{


     return ucwords(str_replace('-', ' ', $value));
}

//ads place spot

function ads_space_sport($item_key)
{
     if (\App\Support\Ads::suppressed()) {
          return '';
     }
     $item_key = $item_key + 1;
     if (($item_key % 8 == 0)) {


          return '<div class="col-lg-12 text-center">
                   <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"
                         crossorigin="anonymous"></script>
                    <!-- fixed-ad-hori-1 -->
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="ca-pub-2428477024574809"
                         data-ad-slot="5422510272"
                         data-ad-format="auto"
                         data-full-width-responsive="true"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>';
     }
}

function small_ads_space_sport($item_key)
{
     $item_key = $item_key + 1;
     if (($item_key % 4 == 0)) {
          return '<div class="col-lg-12 text-center">
                    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"
                             crossorigin="anonymous"></script>
                        <!-- fixed-ad-hori-1 -->
                        <ins class="adsbygoogle"
                             style="display:block"
                             data-ad-client="ca-pub-2428477024574809"
                             data-ad-slot="5422510272"
                             data-ad-format="auto"
                             data-full-width-responsive="true"></ins>
                        <script>
                             (adsbygoogle = window.adsbygoogle || []).push({});
                        </script>
                </div>';
     }
}

function bottom_single_ads()
{
     if (\App\Support\Ads::suppressed()) {
          return '';
     }
     return '<div class="col-lg-12 text-center">
                        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"
                             crossorigin="anonymous"></script>
                        <!-- mobile-bottom-ads -->
                        <ins class="adsbygoogle"
                             style="display:inline-block;width:320px;height:50px"
                             data-ad-client="ca-pub-2428477024574809"
                             data-ad-slot="9817390780"></ins>
                        <script>
                             (adsbygoogle = window.adsbygoogle || []).push({});
                        </script>
                </div>';
}
function bottom_double_ads()
{


     return '<div class="col-lg-12 text-center">
                </div>';
}

function single_ads_show()
{
     if (\App\Support\Ads::suppressed()) {
          return '';
     }

     return '<div class="col-lg-12 text-center">
                   <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2428477024574809"
                         crossorigin="anonymous"></script>
                    <!-- fixed-ad-hori-1 -->
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="ca-pub-2428477024574809"
                         data-ad-slot="5422510272"
                         data-ad-format="auto"
                         data-full-width-responsive="true"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>';
}


function div_background()
{
     $a = array("gradient", "gradient", "gradient");
     return $a[array_rand($a)];
}

//end section

if (!function_exists('active_menu')) {
     function active_menu($url)
     {

          $segment = request()->segment(3);

          // the module's own dashboard page has no 3rd segment (e.g. /admin/rrb)
          if ($url === 'dashboard' && $segment === null)

               return 'active';

          if ($segment !== null && $segment == $url)

               return 'active';
     }
}

if (!function_exists('active_module')) {
     /** Highlights a tab of the main admin sidebar by the module in /admin/<module>. */
     function active_module($module)
     {
          $segment = request()->segment(2);

          if ($module === 'dashboard') {
               return ($segment === null || $segment === 'dashboard') ? 'active' : '';
          }

          return $segment === $module ? 'active' : '';
     }
}



if (!function_exists('bottom_navbar')) {
     function bottom_navbar($url)
     {

          if (((request()->segment(1) == 'kpsc') || (request()->segment(2) == '')) ||
               (request()->segment(2) == 'feed')
          )

               return true;
     }
}




if (!function_exists('DateTimeFormat')) {
     function DateTimeFormat($date)
     {
          if ($date == '') {
               return false;
          }
          return date('d-M-Y h:i:a', strtotime($date));
     }
}

if (!function_exists('format_bytes')) {
     function format_bytes($bytes)
     {
          $bytes = (float) $bytes;
          foreach (['B', 'KB', 'MB', 'GB', 'TB'] as $unit) {
               if ($bytes < 1024 || $unit === 'TB') {
                    return round($bytes, $unit === 'B' ? 0 : 2) . ' ' . $unit;
               }
               $bytes /= 1024;
          }
     }
}


if (!function_exists('active_exam_menu')) {
     /** Highlights a sidebar item of the common exam pages: admin/exam/{exam}/{page}. 'dashboard' = the exam's home. */
     function active_exam_menu($page)
     {
          $segment = request()->segment(4);

          if ($page === 'dashboard') {
               return ($segment === null && request()->segment(2) === 'exam') ? 'active' : '';
          }

          return $segment === $page ? 'active' : '';
     }
}


if (!function_exists('admin_coupons')) {
     /** Coupon pages live at admin/coupons (main sidebar). */
     function admin_coupons($path = '')
     {
          return url('admin/coupons' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
     }
}
