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
	
	Class oProducts extends ODBO {
		
		public function __construct(){
			
			$this->table = "oproducts";
			$this->table_definition = array(
				"oproduct_id" => 				array( "primary_key" => TRUE,
					"categories" => "object_id:/m/categories/oCategoryLinks/"
				),
				"oproduct_name" => 				array( "data_type" => "varchar(255)" ),
				"oproduct_description" => 		array( "data_type" => "text" ),
				"oproduct_keywords" => 			array( "data_type" => "varchar(255)" ),
				"oproduct_price" => 			array( "data_type" => "float" ),
				"oproduct_slug" =>				array( "data_type" => "varchar(255)" ),
				"oproduct_parent_id" =>			array( "data_type" => "integer",	'parent'=>"oproduct_id:/m/products/oProducts/" ),
			);
			
			$this->permissions = array(
				"object" => "any",
				"add" => "any",
				"out" => "any"
			);
			
		}
		
		/***********************************************************************
		
			PUBLIC: OUT Function
			
		***********************************************************************/
		
		public function out($params=array()){
			
			if( !empty($_SESSION['path_array']) ){
				
				$product = $this->route('/w/WProducts/OProducts/get/?oproduct_slug='.$_SESSION['path_array'][0])->data; 
				if( !empty($product) ){ $product = $product[0]; array_pop($_SESSION['path_array']); }
				
				$categories = $this->route('/cmd/OCategories/get/?ocategory_type=product')->data;
				
				ob_start();
				include __SELF__ . 'widgets/WProducts/views/v_product_details.php';
				$this->html = ob_get_clean();	
				
			} else {
				
				$categories = $this->route('/cmd/OCategories/get/?ocategory_type=product')->data;
				
				ob_start();
				include __SELF__ . 'widgets/WProducts/views/v_products.php';
				$this->html = ob_get_clean();	
				
			}
			
		}
		
		
	}?>