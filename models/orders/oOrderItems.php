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
	
	Class oOrderItems extends ODBO {
		
		public function __construct(){
			
			$this->table = "oorderitems";
			$this->table_definition = array(				
				"oorder_item_id" => 				array( "primary_key" => TRUE,
					"watches" => "oorder_item_id:/m/oWatches/"
				),
				"oorder_id" =>						array( "data_type" => "integer"			),
				"oproduct_id" => 					array( "data_type" => "integer" 		),
				"oorder_item_date" =>				array( "data_type" => "datetime" 		),
				"oorder_item_parent_description" =>	array( "data_type" => "varchar(100)" 	),
				"oorder_item_description" => 		array( "data_type" => "varchar(100)" 	),
				"oorder_item_quantity" =>			array( "data_type" => "integer"		 	),
				"oorder_item_unit_price" =>			array( "data_type" => "decimal"			),
				"oorder_item_total" => 				array( "data_type" => "decimal"			)
			);
			
		}
				
	}?>