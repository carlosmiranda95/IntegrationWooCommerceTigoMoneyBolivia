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
include_once 'nusoap.php';
if (!defined('ABSPATH')) {
	exit;
}
if (class_exists('WC_Gateway_TigoMoney_Request')) {
	return;
}
class WC_Gateway_TigoMoney_Request {
	protected $gateway;
	protected $notify_url;
	public function __construct($gateway) {
		$this->gateway = $gateway;
		$this->notify_url = WC()->api_request_url('WC_Gateway_TigoMoney');
	}
/*Encriptar y Desencriptar Datos*/
	protected function Encrypt($data, $blocksize = 8) {
		$len = strlen($data);
		$extra = ($len % $blocksize);
		if ($extra > 0) {
			$padding = $blocksize - $extra;
			$data = $data . str_repeat("\0", $padding);
		}
		$encrypted = mcrypt_encrypt("tripledes", $this->gateway->encrypt_key, $data, "ecb");
		return base64_encode($encrypted);
	}
	protected function Decrypt($data) {
		$cipher = base64_decode($data);
		$decrypted = mcrypt_decrypt("tripledes", $this->gateway->encrypt_key, $cipher, "ecb");
		return trim(trim($decrypted), "\0");
	}
/*Encriptar y Desencriptar Datos*/
	public function get_request_url($order, $phonenumber) {
		$tigomoney_args = $this->generate_arguments($order, $phonenumber);
		$encrypted_params = $this->Encrypt($tigomoney_args);
        $client = new nusoap_client($this->gateway->url_service,'wsdl');
        $err = $client->getError();
        $parametro = array('key' => $this->gateway->identity_token, 'parametros' => $encrypted_params);
        $result = $client->call('solicitarPago', $parametro);
        $respDecrypt = $this->Decrypt(implode($result));
        return explode("&",$respDecrypt);
	}
/*Procesa la cadena en base a los parametos recibidos del formulario*/
	protected function generate_arguments($order, $phonenumber) {
		$postmeta = get_post_meta($order->id);
		if (get_woocommerce_currency() != "BOB")
		{
			$tc=$this->gateway->settings['usdbob'];
		} else {
			$tc=1;
		}
		$params = array(
			'pv_nroDocumento' => '',
			'pv_orderId' => $order->id,
			'pv_monto' => $order->get_total()*$tc,
			'pv_linea' => $phonenumber,
			'pv_nombre' => '',
			'pv_urlCorrecto' => esc_url($this->notify_url),
			'pv_urlError' => esc_url($this->notify_url),
			'pv_razonSocial' => $order->billing_company,
			'pv_nit' => $order->billing_nit,
			'pv_items' => '',
		);
        if ($this->gateway->settings['confirmation_message'] !== "")
        {
            $params['pv_confirmacion'] = $this->gateway->settings['confirmation_message'];
        }
        if ($this->gateway->settings['notify_message'] !== "")
        {
            $params['pv_notificacion'] = $this->gateway->settings['notify_message'];
        }
		$item_number = 0;
		if (sizeof($order->get_items()) > 0) {
			foreach ($order->get_items() as $item) {
				$product = $order->get_product_from_item($item);
				$item_quantity = $item['qty'];
				$item_name = $this->item_name($item['name']);
				$item_price = $this->format_price($product->get_price());
				$item_total = $this->format_price($order->get_item_subtotal($item, false));
				$item_number++;
				$params['pv_items'] .= "*i$item_number|$item_quantity|$item_name|$item_price|$item_total";
			}
		}
		return implode(';', array_map(function ($key, $value) {
			return sprintf("%s=%s", $key, $value);
		}, array_keys($params), $params));
	}
	protected function format_price($value) {
		return number_format($value, 2, '.', '');
	}
	protected function item_name($item_name) {
		$item_name = sanitize_text_field($item_name);
		if (strlen($item_name) > 127) {
			$item_name = substr($item_name, 0, 124) . '...';
		}
		return html_entity_decode($item_name, ENT_NOQUOTES, 'UTF-8');
	}
/*Procesa la cadena en base a los parametos recibidos del formulario*/
}
