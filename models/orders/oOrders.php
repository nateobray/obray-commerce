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
	
	Class oOrders extends ODBO {
		
		public function __construct(){
			
			$this->table = "oorders";
			$this->table_definition = array(
				"oorder_id" => 				array( "primary_key" => TRUE,
					
					// Primary Key Withs
					"items" =>		"oorder_id:/m/orders/oOrderItems/",
					"payments" =>	"oorder_id:/m/payments/oPayments/"
				
				),
				"ocustomer_id" =>			array( "data_type" => "integer",	"customer" =>	"ocustomer_id:/m/customers/oCustomers/"		),
				"oaddress_id" => 			array( "data_type" => "integer",	"shipTo" =>		"oaddress_id:/m/location/oAddresses/" 		),
				"ocard_id" => 				array( "data_type" => "integer", 	"card" =>		"ocard_id:/m/payments/oCards/"				),
				"omail_id" =>				array( "data_type" => "integer",	"mail" =>		"omail_id:/m/oMail/" 						),
				"oorder_date" =>			array( "data_type" => "datetime" 	),
				"oorder_status_id" =>		array( "data_type" => "integer", 
					"options" => array(
						0 => "Incomplete",
						1 => "Processing",
						2 => "Pending",
						3 => "Processing - Watch Received",
						4 => "Awaiting Parts",
						5 => "Monitoring - Capacitor",
						6 => "Need Client Reply - Parts",
						7 => "Delivered",
						8 => "Out to Service Center",
						9 => "Awaiting Payment",
						10 => "Monitoring - Parts Installed",
						11 => "Processing - Parts Arrived",
						12 => "Monitoring - Battery",
						13 => "Need Client Reply - Mechanical",
						14 => "Need Client Reply"
					)
				),
				"oorder_customer_name" => 	array( "data_type" => "varchar(50)"	),
				"oorder_customer_email" =>	array( "data_type" => "varchar(50)"	),
				"oorder_customer_phone" => 	array( "data_type" => "varchar(16)"	),
				"oorder_subtotal" => 		array( "data_type" => "decimal" 	),
				"oorder_discount" =>		array( "data_type" => "decimal" 	),
				"oorder_tax" =>				array( "data_type" => "decimal" 	),
				"oorder_total" =>			array( "data_type" => "decimal" 	)
			);
			
			$this->permissions = array(
				"object" => "any",
				"get" => "any",
				"getOptions" => "any"
				
			);
			
		}
				
	}?>