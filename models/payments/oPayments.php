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
	
	Class oPayments extends ODBO {
		
		public function __construct(){
			
			$this->table = "opayments";
			$this->table_definition = array(
				"opayment_id" => 			array( "primary_key" => TRUE 	),
				"ocustomer_id" =>			array( "data_type" => "integer" 	),
				"ocard_id" => 				array( "data_type" => "integer" 	),
				"oorder_id" =>				array( "data_type" => "integer" 	),
				"opayment_date" =>			array( "data_type" => "datetime"	),
				"opayment_total" => 		array( "data_type" => "decimal"		),
				"opayment_status" =>		array( "data_type" => "integer",	"options" => array( 0 => "uncaptured", 1=>"captured", 2=>"failed" )	),
				"opayment_token" =>			array( "data_type" => "varchar(50)" )
			);
			
			$this->permissions = array(
				"object" => "any",
				"add" => "any"
			);
			
		}
		
		public function add( $params=array() ){
			
			parent::add( $params );
			
			if( empty($this->errors) ){
				
				$opayment = $this->data[0];
				$ocustomer = $this->route('/m/customers/oCustomers/get/?ocustomer_id='.$params["ocustomer_id"])->getFirst();
				$ocard = $this->route('/m/payments/oCards/get/?ocard_id='.$params["ocard_id"])->getFirst();
				
				$response = $this->route( "https://api.stripe.com/v1/charges", array(
					"http_method" => 	"post",
					"http_username" => 	__STRIPE_PRIVATE_KEY__,
					"http_raw" => 		TRUE,
					"body" => 			"amount=".number_format($params["opayment_total"]*100,0,'','')."&currency=usd&card=".$ocard->ocard_token."&customer=".$ocustomer->ocustomer_token."&description=".urlencode('#'.$params["oorder_id"])
				));
				
				if( !empty($response->errors) ){
					$this->throwError("There was an error");
					$this->errors = $response->errors;
				} else {
					parent::update(array(
						"opayment_id" => $opayment->opayment_id,
						"opayment_token" => $response->data->id,
						"opayment_status" => ($response->data->paid)?1:2
					));
				}
				
			}
			
		}
		
		public function update( $params=array() ){
			
			parent::update( $params );
			
			if( empty($this->errors) ){
				
				$opayment = $this->data[0];
				if( $opayment->opayment_status != 1 ){
					
					$ocustomer = $this->route('/m/customers/oCustomers/get/?ocustomer_id='.$opayment->ocustomer_id)->getFirst();
					$ocard = $this->route('/m/payments/oCards/get/?ocard_id='.$opayment->ocard_id)->getFirst();
					
					$response = $this->route( "https://api.stripe.com/v1/charges", array(
						"http_method" => 	"post",
						"http_username" => 	__STRIPE_PRIVATE_KEY__,
						"http_raw" => 		TRUE,
						"body" => 			"amount=".number_format($opayment->opayment_total*100,0,'','')."&currency=usd&card=".$ocard->ocard_token."&customer=".$ocustomer->ocustomer_token."&description=".urlencode('#'.$opayment->oorder_id)
					));
					
					if( !empty($response->errors) ){
						$this->throwError("There was an error");
						$this->errors = $response->errors;
					} else {
						parent::update(array(
							"opayment_id" => $opayment->opayment_id,
							"opayment_token" => $response->data->id,
							"opayment_status" => ($response->data->paid)?1:2
						));
					}
					
				}
					
			}
			
		}
		
		public function refund( $params=array() ){
			
		}
				
	}?>