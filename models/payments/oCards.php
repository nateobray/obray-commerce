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
	
	Class oCards extends ODBO {
		
		public function __construct(){
			
			$this->table = "ocards";
			$this->table_definition = array(
				"ocard_id" => 				array( "primary_key" => TRUE 			),
				"ocustomer_id" =>			array( "data_type" => "integer" 		),
				"ocard_fingerprint" =>		array( "data_type" => "varchar(25)"		),
				"ocard_token" => 			array( "data_type" => "varchar(19)" 	),
				"ocard_type" => 			array( "data_type" => "varchar(25)" 	),
				"ocard_last_four" => 		array( "data_type" => "varchar(4)" 		),
				"ocard_status_id" =>		array( "data_type" => "integer",
					"options" => array(
						0 => "Success",
						1 => "CVC Error",
						2 => "Expired",
						3 => "Declined",
						4 => "Processing Error",
						5 => "Uknown Error"
					)
				)
			);
			
			$this->permissions = array(
				"object" => "any",
				"add" => "any"
			);
			
		}
		
		/*******************
			
			curl https://api.stripe.com/v1/customers/cus_7Z3UQGH3wZIfKb/sources/card_17JwBoDlv6ypz9hWiS1gY6WK \
		   -u sk_test_2RpD34JRGjf49AJbgfzaU8dM: \
		   -d name="Jane Austen"
			
		*******************/
		
		public function add( $params=array() ){
			
			parent::add( $params );
			
			if( !empty($_SESSION['customer']) && !empty($params['stripe_token']) ){
				
				$ocard = $this->data[0];
				$ocustomer = $this->route("/m/customers/oCustomers/get/?ocustomer_id=".$_SESSION["customer"]->ocustomer_id)->getFirst();
				$response = $this->route( "https://api.stripe.com/v1/customers/".$ocustomer->ocustomer_token."/sources", array(
					"http_method" => 	"post",
					"http_username" => 	__STRIPE_PRIVATE_KEY__,
					"http_raw" => 		TRUE,
					"body" => 			"source=".$params['stripe_token']
				));
				
				if( empty($response->errors) ){
					$this->data = array( 0 => $ocard );	
				} else {
					
					$this->throwError("there was an error.");
					$this->errors = $response->errors;
					
					if( !empty($response->errors['general'][0]->code) ){
						$ocard_status_id = 0;
						switch($response->errors['general'][0]->code){
							case "incorrect_cvc": $ocard_status_id = 1; break;
							case "expired_card": $ocard_status_id = 2; break;
							case "card_declined": $ocard_status_id = 3; break;
							case "processing_error": $ocard_status_id = 4; break;
							default: $ocard_status_id = 5; break;
								
						}
						
						$response = $this->route("/m/payments/oCards/update/",array(
							"ocard_id" => $ocard->ocard_id,
							"ocard_status_id" => $ocard_status_id
						));
						
					}
					
					
				}
				
				
			}
			
		}
		
		public function save( $params=array() ){
			
			$response = $this->route( "https://api.stripe.com/v1/tokens/".$params["stripe_token"], array(
				"http_method" => 	"get",
				"http_username" => 	__STRIPE_PRIVATE_KEY__,
				"http_raw" => 		TRUE
			));
			
			if( empty($response->errors) ){
				
				$ocard = $this->route('/m/payments/oCards/get/?ocard_fingerprint='.$response->data->card->fingerprint.'&ocard_status_id=0&ocustomer_id='.$_SESSION['customer']->ocustomer_id)->getFirst();
				if( !empty($ocard) ){
					$this->data = array( 0 => $ocard );
				} else {
					$params['ocard_fingerprint'] = $response->data->card->fingerprint;
					$response = $this->route('/m/payments/oCards/add/',$params);
					if( !empty($response->errors) ){
						$this->throwError("there was an error");
						$this->errors = $response->errors;
					} else {
						$this->data = $response->data;
					}
				}
			}
			
		}
				
	}?>