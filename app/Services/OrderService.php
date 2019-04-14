<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\ProductSku;
use App\Models\CouponCode;
use App\Jobs\CloseOrder;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;

class OrderService
{
    public function store(User $user, UserAddress $userAddress, $remark, $items, CouponCode $coupon = null)
    {
        // 如果传入优惠券，则先检查是否可用
        if ($coupon) {
            // 但此时还没有计算出订单总金额，因此先不校验
            $coupon->checkAvailable($user);
        }
        $order = \DB::transaction(function () use ($user, $userAddress, $remark, $items, $coupon) {
            // 更新地址最后使用时间
            $userAddress->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单
            $order = new Order([
                'address' => [
                    'address' => $userAddress->full_address,
                    'zip' => $userAddress->zip,
                    'contact_name' => $userAddress->contact_name,
                    'contact_phone' => $userAddress->contact_phone,
                ],
                'remark' => $remark,
                'total_amount' => 0,
            ]);
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;
            // 遍历用户提交的商品 SKU
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                // 创建一个 OrderItem 并直接与当前订单关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            // 如果有优惠券，则处理
            if ($coupon) {
                // 总金额已经计算出来了，检查是否符合优惠券规则
                $coupon->checkAvailable($user, $totalAmount);
                // 把订单金额修改为优惠后的金额
                $totalAmount = $coupon->getAdjustedPrice($totalAmount);
                // 将订单与优惠券关联
                $order->couponCode()->associate($coupon);
                // 增加优惠券的用量，需再判断返回值
                if ($coupon->changeUsed() <= 0) {
                    throw new CouponCodeUnavailableException('该优惠券已被兑完');
                }
            }

            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;
        });

        // 这里直接使用 dispatch 函数，订单加入队列
        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }
}
