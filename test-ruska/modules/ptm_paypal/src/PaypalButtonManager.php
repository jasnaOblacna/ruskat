<?php
/**
* 2016 - 2017 Presta Theme Maker
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future.
*
*  @author    Presta Theme Maker <presta.theme.maker@gmail.com>
*  @copyright 2016 Presta Theme Maker
*  @license   Do not distribute this module without permission from the author
*/

/**
* Paypal Button Manager class
*/
class PaypalButtonManager
{
    // credentials
    private $user;
    private $pwd;
    private $signature;
    private $bussiness_mail;
    // either use or not sandbox
    private $use_sandbox = false;
    private $data_fields = array();

    /** Response from PayPal indicating validation was successful */
    const VALID = 'VERIFIED';
    /** Response from PayPal indicating validation failed */
    const INVALID = 'INVALID';

    /** Production Postback URL */
    const VERIFY_URI = 'https://api-3t.paypal.com/nvp';
    /** Sandbox Postback URL */
    const SANDBOX_VERIFY_URI = 'https://api-3t.sandbox.paypal.com/nvp';

    public function __construct($user, $pwd, $signature, $bussiness_mail)
    {
        $this->user = urlencode($user);
        $this->pwd = urlencode($pwd);
        $this->signature = urlencode($signature);
        $this->bussiness_mail = $bussiness_mail;
    }

    public function encryptButtonForm()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Connection: Close'));
        curl_setopt($curl, CURLOPT_URL, $this->getPaypalUri().'?'.http_build_query($this->data_fields));
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function setDataFields($lang, $currency_code, $custom, $return, $cancel_return, $notify_url, $products, $shipping_amt, $button_type = 'CART', $button_subtype = 'PRODUCTS')
    {
        $this->data_fields = array(
            "METHOD" => "BMCreateButton",
            "USER" => $this->user,
            "PWD" => $this->pwd,
            "SIGNATURE" => $this->signature,
            "BUTTONCODE" => "ENCRYPTED",
            "BUTTONTYPE" => $button_type,
            "BUTTONSUBTYPE" => $button_subtype,
            // "VERSION" => "65.2",
            // "BUTTONCOUNTRY" => "US",
            // "BUTTONIMAGE" => "reg",
            // "BUYNOWTEXT" => "BUYNOW",
            "L_BUTTONVAR0" => "upload=1",
            "L_BUTTONVAR1" => "no_note=1",
            "L_BUTTONVAR2" => "bussiness={$this->bussiness_mail}",
            "L_BUTTONVAR3" => "lc=$lang",
            "L_BUTTONVAR4" => "currency_code=$currency_code",
            "L_BUTTONVAR5" => "custom=$custom",
            "L_BUTTONVAR6" => "notify_url=$notify_url",
            "L_BUTTONVAR7" => "cancel_return=$cancel_return",
            "L_BUTTONVAR8" => "return=$return",
        );
        
        $counter = 9;
        $product_counter = 1;
        // set dynamic data
        foreach ($products as $product) {
            $this->data_fields["L_BUTTONVAR".$counter] = "item_name_{$product_counter}={$product['name']}";
            $counter++;
            $this->data_fields["L_BUTTONVAR".$counter] = "quantity_{$product_counter}={$product['cart_quantity']}";
            $counter++;
            $this->data_fields["L_BUTTONVAR".$counter] = "item_number_{$product_counter}={$product['id_product']}";
            $counter++;
            $this->data_fields["L_BUTTONVAR".$counter] = "amount_{$product_counter}=". round($product['price_wt'], 2);

            if ($product_counter == 1) {
                if ($shipping_amt > 0) {
                    $counter++;
                    $this->data_fields["L_BUTTONVAR".$counter] = "amount_{$product_counter}={$shipping_amt}";
                }
            }

            $product_counter++;
            $counter++;
        }
        return $this->data_fields;
    }

    /**
     * Determine endpoint to post the verification data to.
     * @return string
     */
    public function getPaypalUri()
    {
        if ($this->use_sandbox) {
            return self::SANDBOX_VERIFY_URI;
        } else {
            return self::VERIFY_URI;
        }
    }

    public function useSandBox($use_sandbox)
    {
        $this->use_sandbox = $use_sandbox;
    }
}