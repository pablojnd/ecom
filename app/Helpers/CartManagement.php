<?php


namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Cookie;

class CartManagement
{
    // Agregar o actualizar un item en el carrito
    public static function addItemToCart($product_id)
    {
        $cart_items = self::getCartItemsFromCookie();
        $existing_item_key = self::findCartItemKey($cart_items, $product_id);

        if ($existing_item_key !== null) {
            // dd($existing_item_key);
            // Si el producto ya está en el carrito, aumenta la cantidad y recalcula el total
            $cart_items[$existing_item_key]['quantity']++;
            $cart_items[$existing_item_key]['total_amount'] = $cart_items[$existing_item_key]['quantity'] * $cart_items[$existing_item_key]['unit_amount'];
        } else {
            // Si el producto no está en el carrito, lo añade con cantidad 1
            $product = Product::find($product_id, ['id', 'product_name', 'price', 'images']);
            if ($product && isset($product->price)) {
                $cart_items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'images' => $product->images[0] ?? null, // Primera imagen o null
                    'quantity' => 1,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price,
                ];
            }
        }

        self::addCartItemsToCookie($cart_items);
        return count($cart_items);
    }

    public static function addItemToCartQty($product_id, $qty = 1)
    {
        $cart_items = self::getCartItemsFromCookie();
        $existing_item_key = self::findCartItemKey($cart_items, $product_id);

        if ($existing_item_key !== null) {
            // Si el producto ya está en el carrito, aumenta la cantidad y recalcula el total
            $cart_items[$existing_item_key]['quantity'] = $qty;
            $cart_items[$existing_item_key]['total_amount'] = $cart_items[$existing_item_key]['quantity'] * $cart_items[$existing_item_key]['unit_amount'];
        } else {
            // Si el producto no está en el carrito, lo añade con cantidad 1
            $product = Product::find($product_id, ['id', 'product_name', 'price', 'images']);
            if ($product && isset($product->price)) {
                $cart_items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'images' => $product->images[0] ?? null, // Primera imagen o null
                    'quantity' => $qty,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price,
                ];
            }
        }

        self::addCartItemsToCookie($cart_items);
        return count($cart_items);
    }

    // Quitar un item específico del carrito
    public static function removeCartItem($product_id)
    {
        $cart_items = self::getCartItemsFromCookie();
        $item_key = self::findCartItemKey($cart_items, $product_id);

        if ($item_key !== null) {
            unset($cart_items[$item_key]);
            self::addCartItemsToCookie($cart_items);
        }

        return $cart_items; // Retorna el array actualizado del carrito
    }

    // Limpiar todo el carrito de la cookie
    public static function clearCartItems()
    {
        Cookie::queue(Cookie::forget('cart_items'));
    }

    // Incrementar cantidad de un item en el carrito
    public static function incrementQuantityToCartItem($product_id)
    {
        return self::adjustQuantity($product_id, 1);
    }

    // Decrementar cantidad de un item en el carrito
    public static function decrementQuantityToCartItem($product_id)
    {
        return self::adjustQuantity($product_id, -1);
    }

    // Obtener todos los items del carrito desde la cookie
    public static function getCartItemsFromCookie()
    {
        $cart_items = json_decode(Cookie::get('cart_items'), true);
        return $cart_items ?? [];
    }

    // Guardar items del carrito en la cookie
    public static function addCartItemsToCookie(array $cart_items)
    {
        Cookie::queue('cart_items', json_encode($cart_items), 60 * 24 * 30); // 30 días
    }

    // Calcular el total general del carrito
    public static function calculateGrandTotal(array $items)
    {
        // Si $items es null, se convierte a un array vacío para evitar errores
        $items = $items ?? [];
        return array_sum(array_map(fn($item) => $item['total_amount'] ?? 0, $items));
    }

    // Método auxiliar para ajustar cantidad de un item
    private static function adjustQuantity($product_id, $amount)
    {
        $cart_items = self::getCartItemsFromCookie();
        $item_key = self::findCartItemKey($cart_items, $product_id);

        if ($item_key !== null && isset($cart_items[$item_key]['unit_amount'])) {
            $cart_items[$item_key]['quantity'] += $amount;

            // Eliminar si la cantidad es menor a 1, de lo contrario recalcular el total
            if ($cart_items[$item_key]['quantity'] < 1) {
                unset($cart_items[$item_key]);
            } else {
                $cart_items[$item_key]['total_amount'] = $cart_items[$item_key]['quantity'] * $cart_items[$item_key]['unit_amount'];
            }
        }

        self::addCartItemsToCookie($cart_items);
        return $cart_items;
    }

    // Buscar el índice de un item en el carrito por product_id
    private static function findCartItemKey(array $cart_items, $product_id)
    {
        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] === $product_id) {
                return $key;
            }
        }
        return null;
    }
}
