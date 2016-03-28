<?php
	/**
	 * This plugin is designed for Atomik Framework to connect to PayPal ExressCheckout Payement API.
	 * It is a very simple solution for acheiving payment process on a merchant site with a cart that contains multiple items for example.
	 * 
	 * URL Documentation
	 * https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
	 */

	class PayPal
	{

		public static $config = array();


		/**
		 * Start this class as a plugin
		 * @param  array $config configuration values
		 * @return none
		 */
		public static function start(&$config)
		{
			$config = array_merge(array(

				// Configuration
				'user'      => '',
				'pwd'       => '',
				'signature' => '',

				'endpoint'  => 'https://api-3t.sandbox.paypal.com/nvp',

				// the lattest version of PayPal's API
				'version' 	=> '112.0',

				// Debug Mode - true for Debug mode, false for Production
				'sandbox' 		=> false,

				// Currency Code for transaction
				'CURRENCY' 		=> 'EUR',
				

				/** Selects the language for the PayPal Page that opens for payment
				 AU – Australia /	AT – Austria /	BE – Belgium /	BR – Brazil /	CA – Canada /	CH – Switzerland 
				 CN – China /	DE – Germany /	ES – Spain  /	GB – United Kingdom /	FR – France /	IT – Italy 
				 NL – Netherlands /	PL – Poland /	PT – Portugal /	RU – Russia /	US – United States */
				'LOCALECODE' => 'FR',

				/** Determines whether or not PayPal displays shipping address fields on the PayPal pages.
					For digital goods, this field is required, and you must set it to 1. It is one of the following values:
					0 – PayPal displays the shipping address on the PayPal pages.
					1 – PayPal does not display shipping address fields whatsoever.
					2 – If you do not pass the shipping address, PayPal obtains it from the buyer's account profile. */
				'NOSHIPPING' => '0',

				/** Enables the buyer to enter a note to the merchant on the PayPal page during checkout.
					The note is returned in the GetExpressCheckoutDetails response and the DoExpressCheckoutPayment response. It is one of the following values:
					0 – The buyer is unable to enter a note to the merchant.
					1 – The buyer is able to enter a note to the merchant */
				'ALLOWNOTE' => '0',

				/** URL for the image you want to appear at the top left of the payment page. The image has a maximum size of 750 pixels wide by 90 pixels high. 
					PayPal recommends that you provide an image that is stored on a secure (https) server. If you do not specify an image, the business name displays */
				'HDRIMG' => '',

				/** Sets the background color for the payment page. By default, the color is white.
					Character length and limitations: 6-character HTML hexadecimal ASCII color code.	 */
				'PAYFLOWCOLOR' => '',

				/** The HTML hex code for your principal identifying color. PayPal blends your color to white in a gradient fill that borders the cart review area of the PayPal checkout user interface.
					Character length and limitation: 6 single-byte hexadecimal characters that represent an HTML hex code for a color */
				'CARTBORDERCOLOR' => '',

				/** A URL to your logo image. Use a valid graphics format, such as .gif, .jpg, or .png. Limit the image to 190 pixels wide by 60 pixels high. 
					PayPal crops images that are larger. PayPal places your logo image at the top of the cart review area.
					Note: PayPal recommends that you store the image on a secure (https) server. Otherwise, web browsers display a message that checkout pages contain non-secure items. */
				'LOGOIMG' => '',

				/** Email address of the buyer as entered during checkout. PayPal uses this value to pre-fill the PayPal membership sign-up portion on the PayPal pages. */
				'EMAIL' => '',

				/** A label that overrides the business name in the PayPal account on the PayPal hosted checkout pages. */
				'BRANDNAME' => '',

				/** Merchant Customer Service number displayed on the PayPal pages. */
				'CUSTOMERSERVICENUMBER' => '',

				), $config);

        self::$config = &$config;

        // register the Object in Atomik Globals so that we can call it like this in actions and views : $this['paypal']->theMethodYouWantToCall();
        Atomik::set('paypal', new PayPal());
		}



		/**
		 * Constructor
		 */
		public function __construct() 
		{
			if(!self::$config['sandbox']) {
				self::$config['endpoint'] = str_replace('sandbox.', '', self::$config['endpoint']);
			}
		}



		/**
		 * Sends a request to PayPal Servers where arguments are passed as an array using cURL functions
		 * @param  string 	$method 		Defines wich method to use ( 'SetExpressCheckout' or 'GetExpressCheckoutDetails' or 'DoExpressCheckoutPayement' )
		 * @param  Array 		$param 		Arguments array
		 * @return Array 						PayPal answer as an array of values
		 */
		private function request($method, $params)
		{
			$params = array_merge($params, array(
				'METHOD'    => $method,
				'VERSION'   => self::$config['version'],
				'USER'      => self::$config['user'],
				'SIGNATURE' => self::$config['signature'],
				'PWD'       => self::$config['pwd']
			));

			$params = http_build_query($params);

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => self::$config['endpoint'],
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $params,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_VERBOSE => 1
			));

			$response = curl_exec($curl);

			preg_match("/TOKEN=.*/i", $response, $matches);
			if (isset($matches[0])) {
				$response = $matches[0];
			}

			$responsearray = array();
			parse_str($response, $responsearray);

			if (curl_errno($curl)) {
				curl_close($curl);
				throw new AtomikException("Error cURL Communication");
				return false;
			}
			else {
				if ($responsearray['ACK'] == 'Success') {
					curl_close($curl);
					return $responsearray;
				}
				else {
					curl_close($curl);
					throw new AtomikException($responsearray['L_LONGMESSAGE0'], 404);
					return false;
				}
			}
		}



		/**
		 * Initiates a PayPal ExpressCheckout Payment : sends a 'SetExpressCheckout' request and waits for PayPal Answer
		 * @param  array 	$vars 	An array of parameters (which contains PayPal mandatory and optional parameters and also the cart or the object to buy)
		 * @return string        	URL of the PayPal Page to redirect your customer to
		 */
		public function setExpressCheckout($vars)
		{
			$params = array(
				// mandatory parameters
				'RETURNURL' => self::$config['RETURNURL'],
				'CANCELURL' => self::$config['CANCELURL'],

				// optional parameters
				'LOCALECODE' => $vars['LOCALECODE'],
				'EMAIL'      => $vars['EMAIL'],
				 
				// cart values
				'PAYMENTREQUEST_0_ITEMAMT'      => $vars['PAYMENTREQUEST_0_ITEMAMT'],
				'PAYMENTREQUEST_0_SHIPPINGAMT'  => $vars['PAYMENTREQUEST_0_SHIPPINGAMT'],
				'PAYMENTREQUEST_0_AMT'          => $vars['PAYMENTREQUEST_0_AMT'],
				'PAYMENTREQUEST_0_CURRENCYCODE' => self::$config['CURRENCYCODE']
			);

			if (self::$config['LOGOIMG'])		{ $params['LOGOIMG'] = self::$config['LOGOIMG']; }
			if (self::$config['BRANDNAME'])	{ $params['BRANDNAME'] = self::$config['BRANDNAME']; }

			// values for each articles in the cart
			$i = 0;
			foreach( $vars['CART'] as $key => $product) {
				$params['L_PAYMENTREQUEST_0_NAME'.$i] = $product['name'];
				$params['L_PAYMENTREQUEST_0_DESC'.$i] = $product['desc'];
				$params['L_PAYMENTREQUEST_0_AMT'.$i]  = $product['amt'];
				$params['L_PAYMENTREQUEST_0_QTY'.$i]  = $product['qty'];
				$i++;
			}

			// To set a Commercial Discount on the cart, treat this as if it was an article with a negative price
			if ($vars['DISCOUNT'] > 0) {
				$params['L_PAYMENTREQUEST_0_NAME'.$i] = 'Discount';
				$params['L_PAYMENTREQUEST_0_DESC'.$i] = 'Commercial Discount';
				$params['L_PAYMENTREQUEST_0_AMT'.$i]  = $vars['DISCOUNT'];
				$params['L_PAYMENTREQUEST_0_QTY'.$i]  = '1';
			}

			$response = $this->request('SetExpressCheckout', $params);

			if ($response) {
				if (!self::$config['sandbox'])
					return 'https://www.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token='.$response['TOKEN'];
				else
					return 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token='.$response['TOKEN'];
			}
			else {
				throw new AtomikException("No response from PayPal");
				return false;
			}
		}



		/**
		 * Retreives transaction details that was validated by the user on PayPal page
		 * @param 	String 	$token 		Security token
		 * @return 	Mixed 	Retourne 	An array containing PayPal answer or false if the request failed
		 */
		public function getExpressCheckoutDetails($token)
		{
			$params = array(
				// mandatory parameters
				'TOKEN' => $token
			);

			return $this->request('GetExpressCheckoutDetails', $params);
		}



		/**
		 * Processes the real payment
		 * @param  array 	$vars 	An array of parameters (which contains PayPal mandatory and optional parameters and also the cart or the object to buy)
		 * @return mixed  			An array that contains PayPal answer or false if the request failed
		 */
		public function doExpressCheckoutPayment($vars)
		{
			// parameters in this method must be the same as in 'setExpressCheckout' method
			
			$params = array(
				// mandatory parameters
				'TOKEN' => $_GET['token'],
				'PAYERID' => $_GET['PayerID'],
				'PAYMENTACTION' => 'Sale',
				 
				// optional parameters
				'LOCALECODE' => self::$config['LOCALECODE'],
				'EMAIL'      => self::$config['EMAIL'],
				 
				// cart values
				'PAYMENTREQUEST_0_ITEMAMT'      => $vars['PAYMENTREQUEST_0_ITEMAMT'],
				'PAYMENTREQUEST_0_SHIPPINGAMT'  => $vars['PAYMENTREQUEST_0_SHIPPINGAMT'],
				'PAYMENTREQUEST_0_AMT'          => $vars['PAYMENTREQUEST_0_AMT'],
				'PAYMENTREQUEST_0_CURRENCYCODE' => self::$config['CURRENCYCODE']
			);

			if (self::$config['LOGOIMG'])		{ $params['LOGOIMG'] = self::$config['LOGOIMG']; }
			if (self::$config['BRANDNAME'])	{ $params['BRANDNAME'] = self::$config['BRANDNAME']; }


			$i = 0;
			foreach( $vars['panier'] as $key => $product) {
				$params['L_PAYMENTREQUEST_0_NAME'.$i] = $product['libelle'];
				$params['L_PAYMENTREQUEST_0_DESC'.$i] = $product['ref'];
				$params['L_PAYMENTREQUEST_0_AMT'.$i] = $product['prix_1'];
				$params['L_PAYMENTREQUEST_0_QTY'.$i] = $vars['qte'][$key];
				$i++;
			}
			if ($vars['promotion'] > 0) {
				$params['L_PAYMENTREQUEST_0_NAME'.$i] = 'Promotion';
				$params['L_PAYMENTREQUEST_0_DESC'.$i] = 'Remise Commerciale';
				$params['L_PAYMENTREQUEST_0_AMT'.$i] = number_format(- $vars['promotion'], 2);
				$params['L_PAYMENTREQUEST_0_QTY'.$i] = '1';
			}

			return $this->request('DoExpressCheckoutPayment', $params);

		}





	}
