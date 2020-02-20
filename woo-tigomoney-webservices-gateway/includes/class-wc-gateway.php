<?php
/**
 * Plugin Name: Woo TigoMoney Integration Service Gateway
 * Description: Payment Gateway for TigoMoney web service in Woocommerce
 * Version: 1.0.0
 * Author: Anonimo
 *
 * @package WC_Gateway_TigoMoney
 * @version 1.0.0
 * @category Gateway
 * @author Anonimo
 */
class WC_Gateway_TigoMoney extends WC_Payment_Gateway {

    public $encrypt_key;
    public $identity_token;
    public $confirmation_message;
    public $notify_message;
    public $url_service;
    public $debug;
    public static $log_enabled = false;
    /*
      Constructor for the gateway.
    */
    public function __construct() {
        $this->id = 'tigomoney';
        $this->icon = plugins_url('images/woocommerce-tigomoney.png', plugin_dir_path(__FILE__));
        $this->has_fields = false;
        $this->supports = array('products');
        $this->method_title = 'Tigo Money';
        $this->method_description = 'Pago en linea via Tigo Money Bolivia';
        $this->order_button_text = 'Pagar con Tigo Money';
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
        // Define user set variables
        $this->title = 'TigoMoney';
        $this->description = $this->get_option('description');
        // Tigomoney variables
        $this->url_service = $this->get_option('url_service');
        $this->identity_token = $this->get_option('identity_token');
        $this->encrypt_key = $this->get_option('encrypt_key');
        $this->confirmation_message = $this->get_option('confirmation_message');
        $this->notify_message = $this->get_option('notify_message');
        $this->debug = 'yes' === $this->get_option('debug', 'no');
        //$this->sandbox = 'yes' === $this->get_option('sandbox', 'yes');
        if (!$this->is_available()) {
            $this->enabled = 'no';
        }
        self::$log_enabled = $this->debug;
        add_action('woocommerce_update_options_payment_gateways_tigomoney', array($this, 'process_admin_options'));
        //add_action('woocommerce_api_wc_gateway_tigomoney', array($this, 'return_handler'));
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
    }
    public function admin_options() {
        if (is_admin()) {
            parent::admin_options();
        }
    }
    // Process the payment and return the result
    public function process_payment($order_id) {
        $order = new WC_Order($order_id);
        return array(
            'result' => 'success',
            'redirect' => add_query_arg(array(
                'order' => $order->id,
                'key' => $order->order_key,
            ), get_permalink(woocommerce_get_page_id('pay'))),
        );
    }

//Procesa la transaccion y retorna la respuesta del servicio web al front-End
    public function receipt_page($order_id) {
        echo '<p>Muchas Gracias, por favor paga con tu Billetera de Tigo Money</p>';
        $order = wc_get_order($order_id);
        $posted = wp_unslash($_POST);
        if (isset($posted['tigomoney-phonenumber'])) {
            $phone = trim($posted['tigomoney-phonenumber']);
            if (preg_match("/^[6-7][0-9]{7}$/", $phone)) {
                $tigomoney = new WC_Gateway_TigoMoney_Request($this);
                $respArray = $tigomoney->get_request_url($order, $posted['tigomoney-phonenumber']);
                    $posted['codRes'] = explode("=", $respArray[0])[1];
                    $posted['mensaje'] = explode("=", $respArray[1])[1];
                    $posted['orderId'] = explode("=", $respArray[2])[1];
                    $posted['transaccion'] = explode("=", $respArray[3])[1];    
                    $posted['nroFactura'] = explode("=", $respArray[4])[1];
                    $posted['nroAutorizacion'] = explode("=", $respArray[5])[1];
                    $posted['codigoControl'] = explode("=", $respArray[6])[1];
                if ($posted['codRes'] == 0 && $posted['codRes'] != null){
                    $this->payment_status_completed($order,$posted);
                }
                else{
                    $this->payment_status_failed($order,$posted);
                }
                exit();
            } else {
                echo '<p class="woocommerce-error">Por favor ingresa un número de movil válido.</p>';
            }
        }
        echo $this->generate_form($order);
    }
    public function generate_form($order) {
        $form = '<form action="" method="post" id="payment-form" target="_top">';
        $form .= '<input type="text" id="id_tigomoney-phonenumber" name="tigomoney-phonenumber" required= value="" /> ';
        $form .= '<br><br>';
        $form .= '<input type="submit" class="button alt" id="submit-payment-form" value="Pagar con TigoMoney" /> ';
        $form .= '<a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">Cancelar</a>';
        $form .= '</form>';
        return $form;
    }
    // Look for the current plugin is ready for use
    public function is_available() {
        if ($this->encrypt_key != '' && $this->identity_token != '') {
            return true;
        }
        return false;
    }
    // Settings for TigoMoney Gateway
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Habilitar Medio de Pago', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Habilitar Tigo Money', 'woocommerce'),
                'default' => 'yes',
            ),
            'debug' => array(
                'title' => __('Registro de desarrollo', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Permitir Registro', 'woocommerce'),
                'default' => 'no',
            ),
            'description' => array(
                'title' => __('Descripción', 'woocommerce'),
                'type' => 'text',
                'desc_tip' => true,
                'description' => __('Describe el método de pago al usuario al finalizar la compra.', 'woocommerce'),
                'default' => __('Paga via TigoMoney.', 'woocommerce'),
            ),
            'url_service' => array(
                'title' => __('Url de Servicio', 'woocommerce'),
                'type' => 'text',
                'desc_tip' => true,
                'description' => __('Url del servicio Tigo Money.', 'woocommerce'),
                'default' => __('', 'woocommerce'),
            ),
            'identity_token' => array(
                'title' => __('Llave de Identificación', 'woocommerce'),
                'type' => 'text',
                'description' => __('Ingrese su llave TigoMoney de Identificación, esta se usara para identificar al comercio dentro de la pasarela de pagos VIPAGOS. ', 'woocommerce'),
                'default' => '',
                'desc_tip' => true,
            ),
            'encrypt_key' => array(
                'title' => __('Llave de Encriptación', 'woocommerce'),
                'type' => 'text',
                'description' => __('Esta se usara para encriptar los parámetros antes de re direccionar a la pasarela de pagos VIPAGOS así como también desencriptar la respuestas de esta misma.', 'woocommerce'),
                'default' => '',
                'desc_tip' => true,
                'placeholder' => '',
            ),
            'confirmation_message' => array(
                'title' => __('Mensaje de Confirmación', 'woocommerce'),
                'type' => 'text',
                'description' => __('Este mensaje será enviado al cliente en el SMS de confirmación cuando el pago se haya realizado de manera exitosa.', 'woocommerce'),
                'default' => 'Mensaje Confirmación',
                'desc_tip' => true,
            ),
            'notify_message' => array(
                'title' => __('Mensaje de Notificación', 'woocommerce'),
                'type' => 'text',
                'description' => __('Mensaje adicional que se enviara en la notificación cuando se realice el cobro.', 'woocommerce'),
                'default' => 'Mensaje Notificacion',
                'desc_tip' => true,
            )
        );
        if (get_woocommerce_currency() != "BOB")
        {
          $this->form_fields['usdbob'] = array(
                  'title' => __('Tipo de Cambio ('.get_woocommerce_currency().') y (BOB)', 'woocommerce'),
                  'type' => 'text',
                  'description' => __('Tipo de cambio entre la moneda del sitio ('.get_woocommerce_currency().') y TigoMoney.', 'woocommerce'),
                  'default' => '1',
                  'desc_tip' => true,
          );
        } else {
          $this->gateway->settings['usdbob'] = 1;
        }
    }

// Output for the order received page.
    public function thankyou_page() {
        if ($this->instructions) {
            echo wpautop(wptexturize($this->instructions));
        }
    }
// Funcion que permite mostrar el mensaje de error en caso de pago no exitoso
    protected function payment_status_failed($order, $posted) {
        $errormessages = array(
            '4' => 'Comercio no Habilitado para el pago con Tigo Money.',
            '7' => 'Acceso denegado por favor intenta nuevamente verificando los datos ingresados.',
            '8' => 'El PIN ingresado es inválido, si olvidaste tu pin, llama al *555 o contáctate con soporte directamente desde la App Tigo Money, si tu saldo es mayor a Bs 313, debes pasar por un of. Tigo con tu carnet.',
            '11' => 'Tiempo agotado, por favor inicia nuevamente la transaccion.',
            '14' => 'Cuenta no habilitada con Tigo Money, regístrate marcando *555# o descarga la App Tigo Money a tu celular. Mas info llama al *555, o contáctate con soporte directamente desde la App Tigo Money.',
            '16' => 'Cuenta Tigo Money suspendida, por favor comunícate al *555, o contáctate con soporte directamente desde la App Tigo Money.',
            '17' => 'El monto solicitado no es válido. Verifica los datos ingresados.',
            '19' => 'Comercio no Habilitado para el pago con Tigo Money.',
            '23' => 'El monto solicitado es inferior al requerido, por favor verifica los datos ingresados.',
            '24' => 'El monto solicitado es superior al requerido, por favor verifica los datos ingresados.',
            '1001' => 'Tu saldo es insuficiente para completar la transaccion, carga tu cuenta desde la web de tu banco, desde un cajero Tigo Money ó desde un Punto más cercano a ti, marcando *555# o ingresando a la App Tigo Money.',
            '1002' => 'Ingresa a Completa tu transaccion desde la App Tigo Money o marcando *555#, Si olvidaste tu PIN, llama al *555, o contáctate con soporte directamente desde la App Tigo Money. Si tu saldo es mayor a Bs 313, debes pasar por un of. Tigo con tu carnet.',
            '1003' => 'Estimado Cliente llego a su limite de monto transaccionado, si tiene alguna consulta comuniquese con el *555',
            '1004' => 'Estimado cliente, llegaste al límite maximo para realizar transacciones, para consultas por favor llama al *555, o contáctate con soporte directamente desde la App Tigo Money. También puedes pasar por una Of. Tigo con tu Carnet.',
            '560' => 'Mismo Monto, Origen y Destino dentro de 1 min  Señor Cliente su transaccion no fue completada favor intente nuevamente en 1 minuto',
        );
        if (array_key_exists('codRes', $posted)) {
            wc_add_notice('Tigo Money > ' . $errormessages[$posted['codRes']], 'error');
        } else {
            wc_add_notice('Tigo Money > Lo sentimos, hubo un error desconocido', 'error');
        }
        $order->update_status('failed', 'Pago rechazado: ' . $posted['codRes'] . ' ' . $posted['mensaje']);
        wp_redirect(wc_get_page_permalink('cart'));
    }
//Funcion para saber si el pago fue realizado exitosamente
    protected function payment_status_completed($order, $posted) {
        if ($order->has_status('completed')) {
            // Aborting, Order is already complete.
            exit;
        }
        // Good
        $order->add_order_note($posted['mensaje']);
        $order->reduce_order_stock();
        $order->payment_complete();
        WC()->cart->empty_cart();
        wc_add_notice('Tigo Money > ' . $posted['mensaje'], 'success');
        wp_redirect($this->get_return_url($order));
    }
}
