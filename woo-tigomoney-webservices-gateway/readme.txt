=== Woo TigoMoney Web Services Gateway ===

Contributors: Carlos Roberto Miranda Rocha
Tags: store, sales, sell, mobile payment, tigo, tigo money, tigo money bolivia, woocommerce, bolivia, ecommerce, e-commerce,
Requires at least: 4.1
Tested up to: 4.5.3
Stable tag: 3.0.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Provides integration between TigoMoney (Bolivia) mobile payments and WooCommerce.

== Description ==
Woo TigoMoney Web services Gateway plugin allows to easily add TigoMoney Bolivia payment option to your Wordpress / Woocommerce website store. Tigo Money Users from Bolivia will be able to pay to your business tigo money account using their virtual mobile wallet money.

You can integrate this to your Wordpress / Woocommerce Store and after applying to Tigo Money Business account start selling your products and services online.

= Features =
* Sell products and services using Tigo Money in your Wordpress / Woocommerce webpage.
* You can set and modify the identification and encriptation key directly in the admin panel.
* The confirmation and notification message are customizable.
* At the Checkout, you must enter your tigo money account number.
* Compatible with Woocommerce and any Woocommerce enabled theme.

== Installation ==
1. For installing the plugin, you need first to install Woocommerce plugin and have it activated.

2. After Woocommerce is working, download Woo TigoMoney Web Services Gateway or select it from the Wordpress Plugin Directory. In the admin panel, select plugin -> install new. Then upload or search it in the Wordpress Plugin Directory.

3. After installation of Tigo Money Plugin, you have to configure the basic settings in the plugin:
Go to WooCommerce -> Settings. In the Checkout panel, select Tigo Money of the different Checkout Options.
* Enable Tigo Money
* Select the title of the payment method for the user (default is TigoMoney)
* Select the description of the payment method for the user (default is Pay with TigoMoney)
* Enter the identification and encryption key provided by Tigo Money Bolivia
* Enter your own Confirmation and Notification message
* In case of problems with the plugin, you can enable the debug log for troubleshooting.

Save the settings and the plugin is insalled. It will appear as an option of purchase for your clients in the checkout screen.

= Usage =
Once the plugin is activated, it enables itself as one of the methods of the WC_Payment_Gateway and let users follow the same steps as the other payments methods.

The user after Tigo Money payment method is selected, will be presented with a form web with the transaction information where the user must enter your tigo money account number and press the button "Pay with Tigo Money", After this, the user will receive a push message where must enter his PIN for confirm the transaction Tigo Money, or confirm the transaction via Tigo Money app available for android and IOS.

After the payment is effective, woocommerce receives a confirmation of the payment and the buying process continues.

== Frequently Asked Questions ==
= Can I accept Tigo Money payments if I have a Tigo Money Wallet?
No, you need a Business Account with Tigo Money. You should register your account with them first.

= Can I use to process payments in Wordpress without installing woocommerce
No, this plugin requires woocommerce to work.

== Screenshots ==
1. Tigo Money Woocommerce Admin Panel Configuration
2. Tigo Money Checkout Process

== Changelog ==

Version 1.0
* Basic funcionality

== Copyright ==

woo-gateway-tigomoney, Copyright 2017 Carlos Roberto Miranda Rocha
woo-gateway-tigomoney is distributed under the terms of the GNU GPL

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

