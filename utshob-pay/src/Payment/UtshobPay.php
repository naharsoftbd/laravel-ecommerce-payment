<?php

namespace AvoRed\UtshobPay\Payment;

use AvoRed\Framework\Payment\Payment as AbstractPayment;
use AvoRed\Framework\Payment\Contracts\Payment as PaymentContract;
use AvoRed\Framework\Models\Database\Configuration;
use AvoRed\Framework\Models\Database\Order;

class UtshobPay extends AbstractPayment implements PaymentContract
{
    
    const CONFIG_KEY = 'payment_utshob_pay_enabled';
    
    /**
     * Identifier for this Payment options.
     *
     * @var string
     */
    protected $identifier = 'utshob-pay';
    
    /**
     * Title for this Payment options.
     *
     * @var string
     */
    protected $name = 'Utshob Pay';
    
    /**
     * Payment options View Path.
     *
     * @var string
     */
    protected $view = 'utshob-pay::utshob-pay';
    
    /**
     * Get Identifier for this Payment options.
     *
     * return string
     */
    public function identifier()
    {
        return $this->identifier;
    }
    
    public function enable()
    {
        $isEnabled = Configuration::getConfiguration(self::CONFIG_KEY);
        if (null === $isEnabled || false == $isEnabled) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get Title for this Payment Option.
     *
     * return boolean
     */
    public function name()
    {
        return $this->name;
    }
    
    /**
     * Payment Option View Path.
     *
     * return String
     */
    public function view()
    {
        return $this->view;
    }
    
    /**
     * Payment Option View Data.
     *
     * return Array
     */
    public function with()
    {
        return [];
    }
    
    /*
     * Process Payment Calculation
     *
     */
    public function process($orderData, $cartProducts, $request)
    {
        //EXECUTE API here if any??
        //dd($order['order_id']);
        $order = Order::find($orderData['order_id']);
        try {

            
            $redirect_url = url('/');
            
            $merchant_id = 'WMX565c2283d85a7';
            $app_name ='nextglobalbd';
            $wmx_username = 'wmx_10534_30077';
            $wmx_password = 'wmx_10534_19570';
            $wmx_app_key = 'c2402b8ce47fde714fe241a361bcc8803147b595';
            $wmx_website_url = 'https://epay.walletmix.com/check-server';

            $getServerDetails = file_get_contents($wmx_website_url);
            $getServerDetails = json_decode($getServerDetails);


            $response_url  = $getServerDetails->url;
            $bank_payment_url = $getServerDetails->bank_payment_url;

            $site_url ='http://www.nextglobalbd.com';

            
            $countryInst = 'BD';

            $order_id = $order->id;
            $cart_info_v2 =  $merchant_id.','.$site_url.','. $app_name;

            $options=base64_encode('s='.$site_url.',i='.$_SERVER['SERVER_ADDR']);


            $billing = $order->billing_address;

            $cus_name = $billing->first_name.' '.$billing->last_name;
            $cus_email =$orderData['user_email'];
            $cus_addr= $billing->address1;
            $cus_addr.= $billing->address2;
            $cus_city = $billing->city;
            $cus_country = $billing->country->name;
            $cus_postcode = $billing->area;
            $cus_phone = $billing->phone;

            $shippingAddress = $order->shipping_address;

            $ship_name = $shippingAddress->first_name." ".$shippingAddress->last_name;
            $ship_add = $shippingAddress->address1;
            $ship_add .= $shippingAddress->address2;
            $ship_city = $shippingAddress->city;
            $ship_postcode = $shippingAddress->area;
            $ship_country = $shippingAddress->country->name;


            //$items = $order->getAllItems();

            $product_wtihquantity='';
            $length=0;
            $quantity=0;
            $total = 0;

            foreach ($order->products as $product) {
                $productInfo = json_decode($product->getRelationValue('pivot')->product_info);
                $qty = $product->getRelationValue('pivot')->qty;
                $price = $product->getRelationValue('pivot')->price;
                $t=$qty*$price;
                $product_wtihquantity.='{'.$qty . 'x' . $productInfo->name . '['.$price.']=['.$t.']}+';
                $quantity+=$qty;
                $length++;
                $total =  $product->getRelationValue('pivot')->price * $product->getRelationValue('pivot')->qty;
            }

            

            /*foreach ($items as $item){

                $qty = (int)$item->getQtyOrdered();
                $price=$item->getPrice();
                $t=$qty*$price;
                $product_wtihquantity.='{'.$qty . 'x' . $item->getName() . '['.$price.']=['.$t.']}+';
                $quantity+=$qty;
                $length++;
            }
            

            $product_wtihquantity.='{shipping rate:'.$order->shipping_option
                .'}-{coupon amount:'.$order->getDiscountAmount().
                '}='.$total.' '.$order->currency_code;*/


            $encodeValue = base64_encode($wmx_username.':'.$wmx_password);
            $auth = 'Basic '.$encodeValue;

            $currencyCode = Configuration::getConfiguration('general_site_currency');
	    $currencyCode='BDT';
	    //dd($currencyCode);
	    $params = array(
                "wmx_id" => $merchant_id,
                "merchant_order_id" => $order_id,
                "merchant_ref_id" => uniqid(),
                "app_name" => $app_name,
                "cart_info" => $cart_info_v2,

                "customer_name" => $cus_name,
                "customer_email" => $cus_email,
                "customer_add" => $cus_addr,
                "customer_city" => $cus_city,
                "customer_country" => $cus_country,

                "customer_postcode" => $cus_postcode,
                "customer_phone" => $cus_phone,

                "shipping_name" => $ship_name,
                "shipping_add" => $ship_add,
                "shipping_city" => $ship_city,
                "shipping_country" => $ship_country,
                "shipping_postCode" => $ship_postcode,

                "product_desc" => $this->remove_amp_charecter($product_wtihquantity),

                "amount" => $total,
                "currency" => $currencyCode,
                "extra_json" => json_encode([]),
                "options" => $options,
                "callback_url" => $redirect_url ,
                "access_app_key" => $wmx_app_key,
                "authorization" => $auth,
            );

            

            $response = $this->httpPost($response_url,$params);
            $response_d = json_decode($response);
	    $status_code = $response_d->statusCode;
	    //dd($response_d);
            $token = $response_d->token;

            if($status_code == 1000){

                $_SESSION["checkout"]["wmx_token"] = $token;

                $message = "Heading to pay by walletmix<br/>";

                
                return $bank_payment_url."/".$token;

                //return true;

            }else{

                echo $response_d->statusMsg;
                exit;
            }

        } catch (\Exception $e) {

                $message = "Exception: " . $e->getMessage();
            echo $message;

        }
        
    }

    protected function httpPost($url,$params){
        $postData = '';
        //create name value pairs seperated by &
        //dd($params);
        foreach($params as $k => $v)
        {
            $postData .= $k . '='.$v.'&';
        }
        $postData = rtrim($postData, '&');
        //dd($postData);
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false);
        //curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $output=curl_exec($ch);

        curl_close($ch);
        return $output;
    }

    public function remove_amp_charecter($params){
        $str = str_replace('&', '-', $params);
        return $str;
    }
}

