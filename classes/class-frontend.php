<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class EDD_GF_Frontend {
	public function __construct() {

		edd_debug_log( 'Gateway Fees: EDD_GF_Frontend running' );

		add_action( 'init', array( $this, 'default_gateway_fee' ) );
		add_action( 'wp_ajax_edd_calculate_gateway_fees', array( $this, 'recalculate_gateway_fees' ) );
		add_action( 'wp_ajax_nopriv_edd_calculate_gateway_fees', array( $this, 'recalculate_gateway_fees' ) );
	}

	function default_gateway_fee() {

		edd_debug_log( 'Gateway Fees: default_gateway_fee() running' );

		EDD()->fees->remove_fee( 'gateway_fee' );

		if ( edd_get_cart_total() == 0 ) {

			edd_debug_log( 'Gateway Fees: cart total was 0' );

			return;
		}

		$gateway = edd_get_default_gateway();

		$fee = $this->calculate_gateway_fee( $gateway );

		$label = edd_get_option( 'edd_gf_label_' . $gateway, edd_get_gateway_checkout_label( $gateway ) . ' ' .__( 'fee', 'edd_gf' ) );

		edd_debug_log( 'Gateway Fees: fee before filter: ' . $fee );

		$fee = apply_filters( 'edd_gf_fee_total_before_add_fee', $fee );

		edd_debug_log( 'Gateway Fees: fee after filter: ' . $fee );

		if ( $fee !== '0' && $fee !== '0.0' && $fee !== '0.00' ) {

			edd_debug_log( 'Gateway Fees: adding fee in default_gateway_fee function' );

			EDD()->fees->add_fee( $fee, $label, 'gateway_fee' );
		}
	}

	function gateway_fee( $gateway = false ) {

		edd_debug_log( 'Gateway Fees: gateway_fee() running' );

		EDD()->fees->remove_fee( 'gateway_fee' );

		if ( edd_get_cart_total() == 0 ) {
			edd_debug_log( 'Gateway Fees: cart total was 0' );
			return;
		}

		$fee = $this->calculate_gateway_fee( $gateway );

		$label = edd_get_option( 'edd_gf_label_' . $gateway, edd_get_gateway_checkout_label( $gateway ) . ' ' .__( 'fee', 'edd_gf' ) );

		edd_debug_log( 'Gateway Fees: fee before filter: ' . $fee );

		$fee = apply_filters( 'edd_gf_fee_total_before_add_fee', $fee );

		edd_debug_log( 'Gateway Fees: fee after filter: ' . $fee );

		if ( $fee !== '0' && $fee !== '0.0' && $fee !== '0.00' ) {

			edd_debug_log( 'Gateway Fees: adding fee in gateway_fee function' );

			EDD()->fees->add_fee( $fee, $label, 'gateway_fee' );
		}
	}

	function calculate_gateway_fee( $gateway ) {

		edd_debug_log( 'Gateway Fees: calculate_gateway_fee() running' );

		// get total
		$total = edd_get_cart_total();

		edd_debug_log( 'Gateway is: ' . $gateway );

		// apply % if appl
		$percent =  edd_get_option( 'edd_gf_percent_'.$gateway, '' );

		edd_debug_log( 'Gateway Fees: percent is ' . $percent );

		// sanitize percent
		$percent = preg_replace( '/[^\\d.]+/', '', $percent );

		$fee = 0;

		// apply flat if appl
		$flat = edd_get_option( 'edd_gf_flat_'.$gateway, '' );

		edd_debug_log( 'Gateway Fees: flat rate is ' . $flat );

		// sanitize flat
		$flat = preg_replace( '/[^\\d.]+/', '', $flat );

		edd_debug_log( 'Gateway Fees: sanitized flat rate is ' . $flat );

		if ( ! empty( $flat ) && ! empty( $percent ) ) {
			// paypal style
			$percent = $percent/100;
			$fee     = ( $total + $flat ) / ( 1 - $percent );
			$fee     = round( $fee, 2 );
			$fee     = $fee - $total;
			edd_debug_log( 'Gateway Fees: Paypal style fee is ' . $fee );
		}
		else if ( ! empty( $flat ) ) {
				// simple add flat fee
				$fee     = $flat;
				edd_debug_log( 'Gateway Fees: Simple style fee is ' . $fee );
			}
		else if ( ! empty( $percent ) ) {
				// simple add percentage fee
				$percent = $percent / 100;
				$fee     = ( $total ) / ( 1 - $percent );
				$fee     = round( $fee, 2 );
				$fee     = $fee - $total;
				edd_debug_log( 'Gateway Fees: Percentage style fee is ' . $fee );
			}

		edd_debug_log( 'Gateway Fees: Fee returned from  calculate_gateway_fee: ' . $fee );

		// return total
		return $fee;

	}

	function recalculate_gateway_fees() {

		edd_debug_log( 'Gateway Fees: recalculate_gateway_fees() running' );
		edd_debug_log( $_REQUEST['gateway'] );

		if ( ! empty ( $_REQUEST['action'] ) && $_REQUEST['action'] === 'edd_calculate_gateway_fees' ) {
			$this->gateway_fee( $_REQUEST['gateway'] );
			ob_start();
			edd_checkout_cart();
			$cart = ob_get_contents();
			ob_end_clean();
			$response = array(
				'html'  => $cart,
				'total' => html_entity_decode( edd_cart_total( false ), ENT_COMPAT, 'UTF-8' ),
			);

			edd_debug_log( 'Gateway Fees: response from recalculate_gateway_fees is ' . json_encode( $response ) );

			echo json_encode( $response );

		}
		edd_die();
	}
}
new EDD_GF_Frontend;
