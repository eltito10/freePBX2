<?php
/*
  $Id: paypal.php,v 1.39 2003/01/29 19:57:15 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  include("../lib/epayment/includes/methods/paypal.php");
  class paypal {
    var $code, $title, $description, $enabled;

// class constructor
    function paypal() {
      global $order;

      $this->code = 'paypal';
      $this->title = MODULE_PAYMENT_PAYPAL_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION;
      $this->sort_order = 1;
      $this->enabled = true;

      //$this->form_action_url = 'https://secure.paypal.com/cgi-bin/webscr';
      $this->form_action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYPAL_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      global $order, $currencies, $currency;

      $my_currency = MODULE_PAYMENT_PAYPAL_CURRENCY;
      if (!in_array($my_currency, array('CAD', 'EUR', 'GBP', 'JPY', 'USD'))) {
        $my_currency = 'USD';
      }
      $currencyObject = new currencies();
      $process_button_string = tep_draw_hidden_field('cmd', '_xclick') .
                               tep_draw_hidden_field('business', MODULE_PAYMENT_PAYPAL_ID) .
                               tep_draw_hidden_field('item_name', STORE_NAME) .
                               tep_draw_hidden_field('rm', '2') .
                               tep_draw_hidden_field('no_shipping', '1') .
                               tep_draw_hidden_field('PHPSESSID', session_id()) .
                               tep_draw_hidden_field('amount', number_format(($order->info['total'] - $order->info['shipping_cost']) * $currencyObject->get_value($my_currency), $currencyObject->get_decimal_places($my_currency))) .
                               //tep_draw_hidden_field('shipping', number_format($order->info['shipping_cost'] * $currencyObject->get_value($my_currency), $currencyObject->get_decimal_places($my_currency))) .
                               tep_draw_hidden_field('currency_code', $my_currency) .
                               tep_draw_hidden_field('return', tep_href_link("checkout_process.php?sess_id=".session_id(), '', 'SSL')) .
                               tep_draw_hidden_field('cancel_return', tep_href_link("checkout_payment.php", '', 'SSL'));

      return $process_button_string;
    }
    function get_CurrentCurrency()
    {
        return MODULE_PAYMENT_PAYPAL_CURRENCY;
    }
    function before_process() {
      return false;
    }

    function get_OrderStatus()
    {
        if ($_POST['payment_status']=="")
        {
            return -2;
        }
        switch($_POST['payment_status'])
        {
            case "Failed":
                return -2;
            break;
            case "Denied":
                return -1;
            break;
            case "Pending":
                return -0;
            break;
            case "In-Progress":
                return 1;
            break;
            case "Completed":
                return 2;
            break;
            case "Processed":
                return 3;
            break;
            case "Refunded":
                return 4;
            break;
            default:
              return 5;
        }
    }
    function after_process() {
      return false;
    }

    function output_error() {
      return false;
    }
    function keys() {
      return array('MODULE_PAYMENT_PAYPAL_STATUS', 'MODULE_PAYMENT_PAYPAL_ID','MODULE_PAYMENT_PAYPAL_CURRENCY');
    }
  }
?>
