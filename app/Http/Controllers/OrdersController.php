<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\UserAddress;
use App\Models\Order;
use App\Services\OrderService;
use App\Exceptions\InvalidRequestException;

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
        return $orderService->store(
            $request->user(),
            UserAddress::find($request->input('address_id')),
            $request->input('remark'),
            $request->input('items')
        );
    }

    public function show(Request $request, Order $order)
    {
        $this->authorize('own', $order);
        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

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
}
