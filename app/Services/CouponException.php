<?php

namespace App\Services;

/** A coupon cannot be used; the message is safe to show to the student. */
class CouponException extends \RuntimeException
{
}
