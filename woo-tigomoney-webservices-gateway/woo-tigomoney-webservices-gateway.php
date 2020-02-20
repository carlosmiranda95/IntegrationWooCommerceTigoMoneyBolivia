<?php

/**

 * Plugin Name: Woo TigoMoney Web Services Gateway

 * Description: Payment Gateway for TigoMoney in Woocommerce

 * Version: 1.0.0

 * Author: Carlos Roberto Miranda Rocha

 *

 * @package WC_Gateway_TigoMoney

 * @version 1.0.0

 * @category Gateway

 * @author Carlos Roberto Miranda Rocha

 */



if (!defined('ABSPATH')) {

    exit;

}



if (!class_exists('WC_TigoMoney')):



    class WC_TigoMoney {

        const VERSION = '3.0.0';



        /**

         * @var mixed

         */

        protected static $instance = null;



        public function __construct() {

            if (class_exists('WC_Payment_Gateway')) {

                include_once 'includes/class-wc-gateway-request.php';

                include_once 'includes/class-wc-gateway.php';

                add_filter('woocommerce_payment_gateways', array($this, 'add_Gateway'));

            }

        }



        /**

         * @param $methods

         * @return mixed

         */

        public function add_Gateway($methods) {

            $methods[] = 'WC_Gateway_TigoMoney';

            return $methods;

        }



        /**

         * @return WcTigomoney - Main instance

         */

        public static function get_Instance() {

            if (null == self::$instance) {

                self::$instance = new self();

            }



            return self::$instance;

        }



    }

    add_filter( 'woocommerce_currencies', 'add_bob_currency' );

    add_filter('woocommerce_currency_symbol', 'add_bob_currency_symbol', 10, 2);

    add_action('plugins_loaded', array('WC_TigoMoney', 'get_Instance'), 0);

endif;



function add_bob_currency( $currencies ) {

 $currencies['BOB'] = __( 'Bolivian Boliviano (Bs.)', 'woocommerce' );

 return $currencies;

}

function add_bob_currency_symbol( $currency_symbol, $currency ) {

 switch( $currency ) {

 case 'BOB': $currency_symbol = 'Bs.'; break;

 }

 return $currency_symbol;

}

