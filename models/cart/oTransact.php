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
	
	Class oTransact extends OObject {
		
		public function __construct(){
			
			$this->permissions = array(
				"object" => "any",
				"relay" => "any"
			);
			
		}
		
		public function relay( $params=array() ){
			$url = "https://secure.paymentclearing.com/cgi-bin/rc/xmltrans2.cgi";

			$this->route($url."?http_method=post&http_content_type=text/xml&http_accept=text/xml",file_get_contents('php://input'));
			echo $this->data;
			exit();
		}
		
	}?>