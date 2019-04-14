<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;

class CouponCode extends Model
{
    // 用常量的方式定义支持的优惠券类型
    const TYPE_FIXED   = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
        self::TYPE_FIXED   => '固定金额',
        self::TYPE_PERCENT => '比例',
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'total',
        'used',
        'min_amount',
        'not_before',
        'not_after',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    protected $dates = ['not_before', 'not_after'];

    protected $appends = [
        'description'
    ];

    public function getDescriptionAttribute()
    {
        $str = '';

        if ($this->min_amount > 0) {
            $str = '满 '.str_replace('.00', '', $this->min_amount);
        }
        if ($this->type === self::TYPE_PERCENT) {
            return $str.' 优惠 '.str_replace('.00', '', $this->value).'%';
        }

        return $str.' 减 '.str_replace('.00', '', $this->value);
    }

    /**
     * 生成优惠码
     * @param  integer $length 优惠码长度
     * @return string
     */
    public static function findAvailableCode($length = 16)
    {
        do {
            // 生成一个指定长度的随机字符串，并转成大写
            $code = strtoupper(Str::random($length));
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * 检查优惠券
     * @param  float|null $orderAmount 订单需支付的金额
     * @return CouponCodeUnavailableException|null
     */
    public function checkAvailable($orderAmount = null)
    {
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }

        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('该优惠券已被兑完');
        }

        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券现在还不能使用');
        }

        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券已过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('订单金额不满足该优惠券最低金额');
        }
    }

    /**
     * 计算优惠后的订单价格
     * @param  float $orderAmount 要优惠的价格
     * @return float              优惠后的价格
     */
    public function getAdjustedPrice($orderAmount)
    {
        // 固定金额
        if ($this->type === self::TYPE_FIXED) {
            // 为了保证系统健壮性，我们需要订单金额最少为 0.01 元
            return max(0.01, $orderAmount - $this->value);
        }

        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }

    /**
     * 新增或减少优惠券使用量
     * @param  boolean $increase true 代表新增|false 代表减少
     * @return [type]            [description]
     */
    public function changeUsed($increase = true)
    {
        if ($increase) {
            // 与检查 SKU 库存类似，这里需要检查当前用量是否已经超出总量
            return $this->newQuery()
                ->where('id', $this->id)
                ->where('used', '<', $this->total)
                ->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}
