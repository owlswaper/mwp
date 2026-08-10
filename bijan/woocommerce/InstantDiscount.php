<?php
namespace Bijan\WooCommerce;

use Bijan\Utils\Product;

class InstantDiscount {
	public static function submit_order( $order_id ) {
		$order = wc_get_order( $order_id );
		if( !in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ), true ) ) return;

		foreach( $order->get_items() as $item ) {
			if( $item->get_type() != 'order_item' ) continue;

			$product_id = $item->get_data()['product_id'];
			$item_quantity = $item->get_data()['quantity'];

			$product_instant_discount = Product::get_instant_discount( $product_id );
			if( $product_instant_discount['total'] === 0 ) continue;

			$remaining_instant_discount = $product_instant_discount['remaining'];

			$already_instant_discount = absint( $item->get_meta( '_reduced_instant_discount_qty', true ) );

			 if( $already_instant_discount != $item_quantity ) {
				$result_qty = $product_instant_discount['remaining'] + $already_instant_discount - $item_quantity;

				Product::save_options( [
					'instant_discount_remaining'	=> $result_qty,
				], $product_id );

				$item->update_meta_data( '_reduced_instant_discount_qty', $item_quantity );
				$item->save();

				$order->add_order_note( sprintf( __( 'Adjusted instant discount quantity for %s: From(%d) &rarr; To(%d)', 'bijan' ), $item->get_name(), $remaining_instant_discount, $result_qty ), false, true );
			}
		}
	}
}

add_action( 'woocommerce_saved_order_items', [InstantDiscount::class, 'submit_order'], 99 );
