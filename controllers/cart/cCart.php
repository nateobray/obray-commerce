<?php

/***********************************************************************
	
Obray - Super lightweight framework.  Write a little, do a lot, fast.
Copyright (C) 2013  Nathan A Obray

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

***********************************************************************/

if (!class_exists( 'OObject' )) { die(); }

Class cCart extends cRoot {
	
	public function __construct(){
		
		parent::__construct();
		$this->permissions = array(
			"object" => "any",
			"index" => "any",
			"add" => "any",
			"clear" => "any",
			"address" => "any",
			"card" => "any",
			"account" => "any",
			"summary" => "any",
			"order" => "any",
			"receipt" => "any",
			"receiptEmail" => "any"
		);

		if( empty($_SESSION['cart']) ){
			$_SESSION["cart"] = new stdClass();
			$_SESSION["cart"]->items = array();
			$_SESSION["cart"]->total = 0;
		}
		
		if( empty($_SESSION["shipTo"]) ){
			$_SESSION["shipTo"]	= array();
		} 
		
		$this->js("serialize,jquery.payment.min.js,oRouter,checkout");
		$this->css("cart");
		
		
	}
	
	/***********************************************************************
	
		PUBLIC: INDEX Function
		
	***********************************************************************/
	
	public function index($params=array()){
		
		if( !empty($_SESSION["customer"]) ){
			$this->set("customer",$_SESSION["customer"]);
		}
		$this->set("shipTo",$_SESSION["shipTo"]);
		$this->set("states",$this->states());
		
		$this->setContentType("text/html");
		$this->load('cart');
		
	}
	
	/***********************************************************************
	
		PUBLIC: Add to cart
		
			1.	Validate parameters
			2.	Retreive product informaiton
			3.	Add to cart in session
			4.	Total up cart
		
	***********************************************************************/
	
	public function add( $params=array() ){
		
		/*******************************************************************
			1.	Validate parameters
		*******************************************************************/
		
		if( empty($params["oproduct_id"]) ){ 				$this->throwError("Invalid Product selected.");						}
		if( empty($params['watch_brand_or_model']) ){		$this->throwError("Please enter your watch's Brand/Model.");		}
		if( empty($params['watch_serial_number']) ){		$this->throwError("Please enter your watch's serial number.");		}
		if( empty($params['watch_caseback_number']) ){		$this->throwError("Please enter your watch's caseback number.");	}
		if( empty($params['watch_condition']) ){			$this->throwError("Please enter your watch's condition.");			}
		
		if( empty($this->errors) ){
			
			/***************************************************************
				2.	Retreive product informaiton
			***************************************************************/
			
			$oproduct = $this->route('/m/products/oProducts/get/?oproduct_id='.$params['oproduct_id'].'&with=parent')->getFirst();
			
			/***************************************************************
				3.	Add to cart in session
			***************************************************************/
			
			if( empty($_SESSION['cart']->items[$oproduct->oproduct_id]) ){
				$_SESSION['cart']->items[$oproduct->oproduct_id] = new stdClass();	
				$_SESSION['cart']->items[$oproduct->oproduct_id]->quantity = 0;
				$_SESSION['cart']->items[$oproduct->oproduct_id]->watches = array();
			}
			++$_SESSION['cart']->items[$oproduct->oproduct_id]->quantity;
			$_SESSION['cart']->items[$oproduct->oproduct_id]->oproduct = $oproduct;
			$_SESSION['cart']->items[$oproduct->oproduct_id]->item_total = $oproduct->oproduct_price * $_SESSION['cart']->items[$oproduct->oproduct_id]->quantity;
			
			$watch = new stdClass();
			$watch->watch_brand_or_model = 		$params['watch_brand_or_model'];
			$watch->watch_serial_number = 		$params['watch_serial_number'];
			$watch->watch_caseback_number = 	$params['watch_caseback_number'];
			$watch->watch_condition = 			$params['watch_condition'];
			
			$_SESSION['cart']->items[$oproduct->oproduct_id]->watches[] = $watch;
			
			$this->data = $_SESSION['cart']->items[$oproduct->oproduct_id];
		}
		
		/*******************************************************************
			4.	Total up cart
		*******************************************************************/
		
		$this->total();
	}
	
	/***********************************************************************
	
		PUBLIC: Total items in the cart
		
	***********************************************************************/
	
	public function total(){
		$total = 0;
		forEach( $_SESSION["cart"]->items as $item ){ $total += $item->item_total; }
		$_SESSION["cart"]->total = $total;
		if( empty($this->data) ){ $this->data = new stdClass(); }
		$this->data->total = $_SESSION["cart"]->total;
	}
	
	public function remove( $params=array() ){
		
	}
	
	public function clear( $params=array() ){
		unset( $_SESSION['cart'] );
	}
	
	/***********************************************************************
	
		Address:  Save customer & Address information to the database
				  and return that information to 
		
	***********************************************************************/
	
	public function address( $params=array() ){
		
		if( empty( $params['ocustomer_name'] ) ){	$this->throwError("Please enter you name.",500,"customerError");	}
		if( empty( $params['ocustomer_email'] ) ){	$this->throwError("Please enter you email.",500,"customerError");	}
		if( empty( $params['ocustomer_phone'] ) ){	$this->throwError("Please enter you phone.",500,"customerError");	}
		
		if( empty( $params['oaddress_name'] ) ){ $this->throwError("Please enter a name for this address.",500,"shippingError"); }
		if( empty( $params['oaddress_street_1'] ) ){ $this->throwError("Please enter the street 1 for this address.",500,"shippingError"); }
		if( empty( $params['oaddress_street_2'] ) ){ unset($params['oaddress_street_2']); }
		if( empty( $params['oaddress_city'] ) ){ $this->throwError("Please enter the city for this address.",500,"shippingError"); }
		if( empty( $params['oaddress_state'] ) ){ $this->throwError("Please enter the state for this address.",500,"shippingError"); }
		if( empty( $params['oaddress_zip'] ) ){ $this->throwError("Please enter the ZIP for this address.",500,"shippingError"); }
		
		if( empty($this->errors) ){
			
			$ocustomer = $this->route('/m/customers/oCustomers/get/?ocustomer_email='.$params['ocustomer_email'])->getFirst();
			
			if( !empty( $_SESSION['customer']->ocustomer_id ) ){
				$params['ocustomer_id'] = $_SESSION['customer']->ocustomer_id;
				$response = $this->route('/m/customers/oCustomers/update/',$params);
			} else if ( !empty($ocustomer) ) {
				$params['ocustomer_id'] = $ocustomer->ocustomer_id;
				$response = $this->route('/m/customers/oCustomers/update/',$params);
			} else {
				$response = $this->route('/m/customers/oCustomers/add/',$params);
			}
			
			if( empty($response->errors) ){
				$_SESSION['customer'] = $response->data[0];
			} else {
				$this->throwError("There was an error.");
				$this->errors = $response->errors;
				return;
			}
			
			if( !empty($_SESSION['shipTo']->oaddress_id) ){
				$params['oaddress_id'] = $_SESSION['shipTo']->oaddress_id;
				$response = $this->route('/m/location/oAddresses/update/',$params);
			} else {
				$response = $this->route('/m/location/oAddresses/add/',$params);
			}
			
			if( empty($response->errors) ){
				$_SESSION['shipTo'] = $response->data[0];
				$this->data = $_SESSION;
				$_SESSION['customer'] = $this->route('/m/customers/oCustomers/update/?ocustomer_id='.$_SESSION['customer']->ocustomer_id.'&oaddress_id='.$response->data[0]->oaddress_id)->getFirst();
			} else {
				$this->throwError("There was an error.");
				$this->errors = $response->errors;
			}
			
		}
		
	}
	
	/***********************************************************************
	
		Card:  Save credit card information 
		
	***********************************************************************/
	
	public function card( $params=array() ){
		
		if( !empty($_SESSION['customer']->ocustomer_id) ){ $params['ocustomer_id'] = $_SESSION['customer']->ocustomer_id; }
		
		$response = $this->route('/m/payments/oCards/save/',$params);
		
		if( empty($response->errors) ){
			$_SESSION['card'] = $response->data[0];
			$this->data = $_SESSION['card'];
			
			if( !empty($params['stripe_token']) ){
				$this->route('/m/customers/oCustomers/update/',array(
					"ocustomer_id" => 	$_SESSION['card']->ocustomer_id,
					"ocard_id" => 		$_SESSION['card']->ocard_id,
					"ocard_token" =>	$params['stripe_token']
				));
			}
			
		} else {
			$this->throwError("Unable to process credit card.");
			$this->errors = $response->errors;
		}
		
	}
	
	public function account( $params=array() ){
		if( empty($params["ouser_password"]) || empty($params["ouser_password_confirm"]) ){
			$this->throwError("Password field empty.",500);
		} else if( $params["ouser_password"] != $params["ouser_password_confirm"] ){
			$this->throwError("Passwords don't match.",500);
		} else {
			
			$ouser = $this->route("/m/oUsers/get/?ouser_email=".$_SESSION["customer"]->ocustomer_email)->getFirst();
			if( !empty($ouser) ){
				$login = $this->route("/m/oUsers/login/?ouser_email=".$_SESSION["customer"]->ocustomer_email.'&ouser_password='.$params["ouser_password"]);
			} else {
				$ouser = $this->route("/m/oUsers/add/?ouser_email=".$_SESSION["customer"]->ocustomer_email."&ouser_permission_level=1&ouser_password=".$params["ouser_password"]);
				$this->data = new stdClass();
				$this->data->ouser = $ouser;
				$login = $this->route("/m/oUsers/login/?ouser_email=".$_SESSION["customer"]->ocustomer_email."&ouser_password=".$params["ouser_password"]);
			}
			
			if( !empty($login->errors) ){
				$this->errors = $login->errors;
				$this->throwError("Unable to login.  A user exists with this email but the password entered doesn't match.  Please try logging in to continue.",500);
			} else {
				$_SESSION["customer"] = $this->route("/m/customers/oCustomers/update/?ocustomer_id=".$_SESSION["customer"]->ocustomer_id."&ouser_id=".$_SESSION["ouser"]->ouser_id)->getFirst();
			}
			
		}
	}
	
	public function summary( $params=array() ){
		if( !empty($_SESSION) ){
			$this->set("session",$_SESSION);
			$this->load("summary",FALSE);
		}
	}
	
	public function order( $params=array() ){
		
		$oorder_params = array(
			"oorder_date" =>			date("Y-m-d H:i:s"),
			"oorder_customer_name" => 	$_SESSION["customer"]->ocustomer_name,
			"oorder_customer_email" => 	$_SESSION["customer"]->ocustomer_email,
			"oorder_customer_phone" => 	$_SESSION["customer"]->ocustomer_phone,
			"oorder_status_id" =>		0,
			"ocustomer_id" =>			$_SESSION["customer"]->ocustomer_id,
			"oaddress_id" =>			$_SESSION["shipTo"]->oaddress_id,
			"ocard_id" =>				$_SESSION["card"]->ocard_id,
			"oorder_subtotal" => 		$_SESSION["cart"]->total,
			"oorder_discount" =>		0,
			"oorder_tax" =>				0,
			"oorder_total" =>			$_SESSION["cart"]->total
		);
		if( !empty($_SESSION["order"]) ){
			$oorder_params["oorder_id"] = $_SESSION["order"]->oorder_id;
			$response = $this->route("/m/orders/oOrders/update/",$oorder_params);
		} else {
			$response = $this->route("/m/orders/oOrders/add/",$oorder_params);	
		}
		
		if( empty($response->errors) ){
			$_SESSION["order"] = $response->data[0];
		} else {
			$this->throwError("There was an error");
			$this->errors = $response->errors;
			return;
		}
		
		
		forEach( $_SESSION["cart"]->items as $id => $item ){
			$oorder_item_params = array(
				"oorder_id" => 							$_SESSION["order"]->oorder_id,
				"oproduct_id" => 						$id,
				"oorder_item_date" => 					date("Y-m-d"),
				"oorder_item_parent_description" =>		$item->oproduct->parent[0]->oproduct_name,
				"oorder_item_description" =>			$item->oproduct->oproduct_name,
				"oorder_item_quantity" =>				$item->quantity,
				"oorder_item_unit_price" =>				$item->oproduct->oproduct_price,
				"oorder_item_total" =>					$item->item_total
			);
			
			if( !empty($_SESSION["cart"]->items[$id]->oorder_item_id) ){
				$oorder_item_params["oorder_item_id"] = $_SESSION["cart"]->items[$id]->oorder_item_id;
				$response = $this->route('/m/orders/oOrderItems/update/',$oorder_item_params);	
			} else {
				$response = $this->route('/m/orders/oOrderItems/add/',$oorder_item_params);
			}
			
			if( empty($response->errors) ){
				
				$_SESSION["cart"]->items[$id]->oorder_item_id = $response->data[0]->oorder_item_id;
				
				forEach( $item->watches as $k => $watch ){
					$watch_params = array(
						"oorder_id" =>					$_SESSION["order"]->oorder_id,
						"oorder_item_id" =>				$response->data[0]->oorder_item_id,
						"ocustomer_id" =>				$_SESSION['customer']->ocustomer_id,
						"owatch_brand" =>				$watch->watch_brand_or_model,
						"owatch_serial_number" =>		$watch->watch_serial_number,
						"owatch_caseback_number" =>		$watch->watch_caseback_number,
						"owatch_condition" =>			$watch->watch_condition
						
					);
								
					if( !empty($watch->owatch_id) ){
						$watch_params["owatch_id"] = $watch->owatch_id;
						$response = $this->route("/m/oWatches/update/",$watch_params);
					} else {
						$response = $this->route("/m/oWatches/add/",$watch_params);
					}
					
					if( !empty($response->errors) ){
						$this->throwError("There was an error");
						$this->errors = $response->errors;
					} else {
						$_SESSION["cart"]->items[$id]->watches[$k]->owatch_id = $response->data[0]->owatch_id;
					}
					
				}
				
			} else {
				$this->throwError("There was an error");
				$this->errors = $response->errors;
			}
			
		}
		
		if( empty($this->errors) ){
			
			$payment_params = array(
				"ocustomer_id" =>	$_SESSION["customer"]->ocustomer_id,
				"oorder_id" => 		$_SESSION["order"]->oorder_id,
				"ocard_id" => 		$_SESSION["card"]->ocard_id,
				"opayment_total" =>	$_SESSION["cart"]->total
			);
			
			if( !empty($_SESSION['cart']->payment->opayment_id) ){
				$payment_params['opayment_id'] = $_SESSION['payment']->opayment_id;
				$response = $this->route("/m/payments/oPayments/update/",$payment_params);		
			} else {
				$response = $this->route("/m/payments/oPayments/add/",$payment_params);		
			}
			
			if( !empty($response->errors) ){
				$this->throwError("there was an error");
				$this->errors = $response->errors;
			} else {
				
				$_SESSION['cart']->payment = $response->data[0];
				
				$this->data = $_SESSION['oorder'] = $this->route('/m/orders/oOrders/get/?oorder_id='.$_SESSION["order"]->oorder_id.'&with=customer|shipTo|items|card|payments')->getFirst();
				
				$this->set("oorder",$_SESSION["oorder"]);
				$this->load("receiptEmail",FALSE);
				
				$this->setContentType("application/json");
				
				$omail_params = array(
					"omail_to" => $_SESSION["order"]->oorder_customer_email,
					"omail_from" => __OUTGOING_EMAIL_ADDRESS__,
					"omail_bcc" => __OUTGOING_EMAIL_ADDRESS__,
					"omail_subject" => "Order Receipt #".$_SESSION["oorder"]->oorder_id,
					"omail_message" => $this->html
				);
				
				$response = $this->route("/m/oMail/send/",$omail_params);
				
				if( !empty($response->errors) ){
					$this->throwError("There was an error.");
					$this->errors = $response->errors;
				} else {
					$this->route("/m/orders/oOrders/update/?oorder_id=".$_SESSION["oorder"]->oorder_id."&oorder_status_id=1&omail_id=".$response->data[0]->omail_id);
				}
				
				unset($_SESSION['cart']);
				unset($_SESSION['order']);
				
			}
			
		}
		
		
	}
	
	public function receipt( $params=array() ){
		
		$this->setContentType("text/html");
		$this->load("receipt");
		
	}
	
	public function receiptEmail( $params=array() ){
		
		$this->setContentType("text/html");
		$this->set("oorder",$_SESSION["oorder"]);
		$this->load("receiptEmail",FALSE);
	}
	
	private function states(){
		return array(
		    'AL'=>'Alabama',
		    //'AK'=>'Alaska',
		    'AZ'=>'Arizona',
		    'AR'=>'Arkansas',
		    'CA'=>'California',
		    'CO'=>'Colorado',
		    'CT'=>'Connecticut',
		    'DE'=>'Delaware',
		    'DC'=>'District of Columbia',
		    'FL'=>'Florida',
		    'GA'=>'Georgia',
		    //'HI'=>'Hawaii',
		    'ID'=>'Idaho',
		    'IL'=>'Illinois',
		    'IN'=>'Indiana',
		    'IA'=>'Iowa',
		    'KS'=>'Kansas',
		    'KY'=>'Kentucky',
		    'LA'=>'Louisiana',
		    'ME'=>'Maine',
		    'MD'=>'Maryland',
		    'MA'=>'Massachusetts',
		    'MI'=>'Michigan',
		    'MN'=>'Minnesota',
		    'MS'=>'Mississippi',
		    'MO'=>'Missouri',
		    'MT'=>'Montana',
		    'NE'=>'Nebraska',
		    'NV'=>'Nevada',
		    'NH'=>'New Hampshire',
		    'NJ'=>'New Jersey',
		    'NM'=>'New Mexico',
		    'NY'=>'New York',
		    'NC'=>'North Carolina',
		    'ND'=>'North Dakota',
		    'OH'=>'Ohio',
		    'OK'=>'Oklahoma',
		    'OR'=>'Oregon',
		    'PA'=>'Pennsylvania',
		    'RI'=>'Rhode Island',
		    'SC'=>'South Carolina',
		    'SD'=>'South Dakota',
		    'TN'=>'Tennessee',
		    'TX'=>'Texas',
		    'UT'=>'Utah',
		    'VT'=>'Vermont',
		    'VA'=>'Virginia',
		    'WA'=>'Washington',
		    'WV'=>'West Virginia',
		    'WI'=>'Wisconsin',
		    'WY'=>'Wyoming',
		);
	}
		
}?>
