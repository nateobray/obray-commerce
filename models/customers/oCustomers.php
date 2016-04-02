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
	
	Class oCustomers extends ODBO {
		
		public function __construct(){
			
			$this->table = "ocustomers";
			$this->table_definition = array(
				"ocustomer_id" => 			array( "primary_key" => TRUE			),
				"ocard_id" =>				array( "data_type" => "integer", 		"card" => "ocard_id:/m/payments/oCards/"			),
				"ouser_id" =>				array( "data_type" => "integer",		"user" => "ouser_id:/m/oUsers/"					),
				"oaddress_id" =>			array( "data_type" => "integer",		"shipTo" => "oaddress_id:/m/location/oAddresses/"	),
				"ocustomer_name" =>			array( "data_type" => "varchar(50)" 	),
				"ocustomer_email" => 		array( "data_type" => "varchar(50)" 	),
				"ocustomer_phone" =>		array( "data_type" => "varchar(16)" 	),
				"ocustomer_token" =>		array( "data_type" => "varchar(19)"		)
			);
			
			$this->permissions = array(
				"object" => "any",
				"add" => "any",
				"get" => 1
			);
			
		}
		
		public function add( $params=array() ){
			
			parent::add( $params );
			
			if( empty($this->errors) ){
				
				$ocustomer = $this->data[0];
				$response = $this->route( "https://api.stripe.com/v1/customers", array(
					"http_method" => 	"post",
					"http_username" => 	__STRIPE_PRIVATE_KEY__,
					"http_raw" => 		TRUE,
					"body" => 	"description=".urlencode($params["ocustomer_name"])."&email=".urlencode($params["ocustomer_email"])
				) );
				
				if( !empty($response->errors) ){
					$this->throwError("Error");
					$this->errors = $response->errors;
				} else {
					$this->data = $this->route("/m/customers/oCustomers/update/?ocustomer_id=".$ocustomer->ocustomer_id.'&ocustomer_token='.$response->data->id )->data;
				}
				
			}
			
		}
		
		public function update( $params=array() ){
			
			parent::update($params);
			
			if( empty($this->errors) ){
				
				$ocustomer = $this->data[0];
				$response = $this->route( "https://api.stripe.com/v1/customers/".$ocustomer->ocustomer_token, array(
					"http_method" => 	"post",
					"http_username" => 	__STRIPE_PRIVATE_KEY__,
					"http_raw" => 		TRUE,
					"body" => 			"description=".urlencode($ocustomer->ocustomer_name)."&email=".urlencode($ocustomer->ocustomer_email)
				));
				
				// (!empty($params["ocard_token"])?("&source=".$params["ocard_token"]):"")
				
				if( !empty($response->errors) ){
					$this->throwError("Error");
					$this->errors = $response->errors;
				} else {
					parent::update(
						array(
							"ocustomer_id" => 		$ocustomer->ocustomer_id,
							"ocustomer_token" =>	$response->data->id
						)
					);
				}
				
				if( !empty($this->data[0]) ){
					$this->data[0]->stripe_call = "https://api.stripe.com/v1/customers/".$ocustomer->ocustomer_token;
				} else {
					$this->data = $response;
				}
				
			}
			
		}
				
	}?>