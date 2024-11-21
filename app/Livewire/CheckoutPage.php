<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use App\Enums\PayStatus;
use App\Enums\OrderStatus;
use Livewire\Attributes\Title;
use App\Helpers\CartManagement;

#[Title('Checkout')]
class CheckoutPage extends Component
{
    public $name;
    public $phone;
    public $email;
    public $street_address;
    public $city;
    public $state;
    public $payment_method;

    public function placeOrder()
    {
        // dd($this->payment_method);
        $this->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'street_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'payment_method' => 'required',
        ]);

        $cart_items = CartManagement::getCartItemsFromCookie();
        $line_tems = [];
        foreach ($cart_items as $item) {
            $line_tems[] = [
                'price_data' => [
                    'unit_amount' => $item['unit_amount'] * 100,
                    'product_data' => [
                        'prooduct_name' => $item['name']
                    ]
                ],
                'quantity' => $item['quantity'],
            ];
        }
        $order = new Order();
        // $order->name = $this->name
        $order->user_id = auth()->user()->id;
        $order->pay_status = PayStatus::Pending;
        $order->pay_method = $this->pay_method;
        $order->order_status = OrderStatus::Processing;
        $order->grand_total = CartManagement::calculateGrandTotal($cart_items);
        $order->notes = 'ORder Creada por ' . auth()->user()->name;

        $redirect_url = '';

        if ($this->payment_method == 'webpay') {
            dd('transbank webpay');
        } else {
            $redirect_url = route('success');
        }

        $order->save();
        $order->items()->createMany($cart_items);
        CartManagement::clearCartItems();
        return redirect($redirect_url);
    }

    public function render()
    {
        $cart_items = CartManagement::getCartItemsFromCookie();
        $grand_total = CartManagement::calculateGrandTotal($cart_items);
        return view('livewire.checkout-page', [
            'cart_items' => $cart_items,
            'grand_total' => $grand_total,
        ]);
    }
}
