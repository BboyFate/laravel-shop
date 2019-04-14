<?php

namespace App\Http\Controllers;

use App\Models\CouponCode;
use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;

class CouponCodesController extends Controller
{
    public function show($code)
    {
        // 判断优惠券是否存在
        if (!$record = CouponCode::query()->where('code', $code)->first()) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }

        $record->checkAvailable();

        return $record;
    }
}
