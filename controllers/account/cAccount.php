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
	
	Class cAccount extends cRoot {
		
		public function __construct(){
			
			parent::__construct();
			
			$this->permissions = array(
				"object" => 1,
				"index" => 1,
				"order" => 1,
				"send" => 1,
				"message" => 0,
				"status" => 0,
				"search" => 0
			);
			
			$this->js("serialize,jquery.payment.min.js,oRouter,oBox,account,oOrder");
			$this->css("obox,account,cart");
			
		}
		
		public function index( $params=array() ){
			
			
			if( $_SESSION["ouser"]->ouser_permission_level == 0 ){ 
				$customer = "";
			} else {
				if( !empty($params["keyword"]) ){
					$customer = " AND ocustomer_id = ".$_SESSION['customer']->ocustomer_id;
				} else {
					$customer = "&ocustomer_id=".$_SESSION['customer']->ocustomer_id;	
				}
			}
			
			if( !empty($params["keyword"]) ){
				$this->set("keyword",$params["keyword"]);
				if( is_numeric($params["keyword"]) ){
					$this->set("oorders",$this->run("SELECT * FROM oorders WHERE oorder_id = ".$params["keyword"]." ".$customer)->data);
					
				} else {
					$this->set("oorders",$this->run("SELECT * FROM oorders WHERE (oorder_customer_name LIKE '%".$params["keyword"]."%' OR oorder_customer_email LIKE '%".$params["keyword"]."%') ".$customer)->data);	
				}
			} else {
				$this->set("oorders",$this->route("/m/orders/oOrders/get/?order_by=oorder_id:DESC".$customer)->data);	
			}
			
			$this->setContentType("text/html");
			$this->load("account");
			
		}
		
		public function order( $params=array() ){
			
			$this->set("oorder",$this->route("/m/orders/oOrders/get/?with=customer|shipTo|card|items|watches|payments&filter=false&oorder_id=".$params["id"])->getFirst() );
			$this->set("omail",$this->route("/m/oMail/get/?with=to|from&oorder_id=".$params["id"])->data);
			$this->set("statuses",$this->route("/m/orders/oOrders/getOptions/?column=oorder_status_id")->data);
			$this->setContentType("text/html");
			$this->load("order");
			
		}
		
		public function send( $params=array() ){
			
			$ouser = $this->route("/m/oUsers/get/?ouser_id=".$params["ouser_to"])->getFirst();
			
			$this->route("/m/oMail/send/",array(
				"oorder_id" => $params["oorder_id"],
				"ouser_to" => $params["ouser_to"],
				"ouser_from" => $params["ouser_from"],
				"omail_to" => $ouser->ouser_email,
				"omail_from" => __OUTGOING_EMAIL_ADDRESS__,
				"omail_bcc" => __OUTGOING_EMAIL_ADDRESS__,
				"omail_subject" => $params["omail_subject"],
				"omail_message" => str_replace("\n","<br/>",$params["omail_message"])
			));
			
			$this->data = $params;
			
		}
		
		public function message( $params=array() ){
			
			$this->data = $this->route("/m/messages/oMessagesDefaults/get/?oorder_status_id=".$params["oorder_status_id"])->data;
			
		}
		
		public function status( $params=array() ){

			$this->data = new stdClass();
			$oorder = $this->route("/m/orders/oOrders/update/?oorder_id=".$params["oorder_id"]."&oorder_status_id=".$params["oorder_status_id"])->getFirst();
			$ocustomer = $this->route("/m/customers/oCustomers/get/?ocustomer_id=".$oorder->ocustomer_id)->getFirst();
			$status = $this->route("/m/orders/oOrders/getOptions/?column=oorder_status_id&key=".$params["oorder_status_id"])->data;
			$this->route("/m/oMail/send/",array(
				"oorder_id" => $oorder->oorder_id,
				"ouser_to" => $ocustomer->ouser_id,
				"ouser_from" => 7,
				"omail_to" => $ocustomer->ocustomer_email,
				"omail_from" => __OUTGOING_EMAIL_ADDRESS__,
				"omail_bcc" => __OUTGOING_EMAIL_ADDRESS__,
				"omail_subject" => "Stewart Time Order Status Update: ".$status,
				"omail_message" => $params["message"]
			));	
			$this->customer = $ocustomer;
			$this->data->status = $params;
			
		}
				
	}?>