<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\SendReviewRequest;
use App\Http\Requests\ApplyRefundRequest;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\CouponCode;
use App\Services\OrderService;
use App\Events\OrderReviewed;
use App\Exceptions\InvalidRequestException;
use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('orders.index', ['orders' => $orders]);
    }

    public function store(OrderRequest $request, OrderService $orderService)
    {
        $coupon = null;

        // 如果用户提交了优惠码
        if ($code = $request->input('coupon_code')) {
            $coupon = CouponCode::query()->where('code', $code)->first();
            if (!$coupon) {
                throw new CouponCodeUnavailableException('优惠券不存在');
            }
        }

        return $orderService->store(
            $request->user(),
            UserAddress::find($request->input('address_id')),
            $request->input('remark'),
            $request->input('items'),
            $coupon
        );
    }

    public function show(Request $request, Order $order)
    {
        $this->authorize('own', $order);
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    /**
     * 确认收货
     * @param  Request $request
     * @param  Order   $order
     * @return Order
     */
    public function received(Request $request, Order $order)
    {
        $this->authorize('own', $order);

        // 判断订单的发货状态是否已发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('发货状态不正确');
        }

        // 更新发货状态为已收到
        $order->update([
            'ship_status' => Order::SHIP_STATUS_RECEIVED
        ]);

        return $order;
    }

    /**
     * 评论页面
     * @param  Order  $order
     * @return [type]        [description]
     */
    public function review(Order $order)
    {
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }

        return view('orders.review', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    /**
     * 商品评论
     * @param  SendReviewRequest $request
     * @param  Order             $order
     * @return [type]                     [description]
     */
    public function sendReview(SendReviewRequest $request, Order $order)
    {
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        if ($order->reviewed) {
            throw new InvalidRequestException('该订单已评价，不可重复提交');
        }
        $reviews = $request->input('reviews');

        \DB::transaction(function () use ($order, $reviews) {
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);

                $orderItem->update([
                    'rating'      => $review['rating'],
                    'review'      => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);
            }
            $order->update([
                'reviewed' => true
            ]);

            event(new OrderReviewed($order));
        });

        return redirect()->back();
    }

    /**
     * 申请退款
     * @param  ApplyRefundRequest $request
     * @param  Order              $order
     * @return Order
     */
    public function applyRefund(ApplyRefundRequest $request, Order $order)
    {
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可退款');
        }
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已经申请过退款，请勿重复申请');
        }

        $extra                  = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');

        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra'         => $extra,
        ]);

        return $order;
    }
}
