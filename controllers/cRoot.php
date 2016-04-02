<?php

   
	if (!class_exists( 'OObject' )) { die(); }

	Class cRoot extends ODBO{

		public function __construct($params=array(),$direct=FALSE){

			parent::__construct();

			//this should point to the sub folder in "views" that will hold your header and footer files
			$this->template = 'main';
			
			//this should be changed to be your default page title.
			$this->set('page_title','Default Page Title');

			$this->permissions = array(
				'object'=>	'any',
				'index'=>	'any',
				'add'=>		'any',
				'update'=>	'any',
				'get'=>		'any',
				'delete'=>	'any',
				'count' => 	'any'
			);
			
			$this->css_array = array();
			$this->js_array = array();
			
			$this->js('jquery.min,bootstrap.min');

		}

		public function set($key,$val){
			$this->variables[$key] = $val;
			return $val;
		}
		
		protected function load($view,$loadTemplate=TRUE){

			if( !empty($this->variables) && count($this->variables) ){
				extract($this->variables);
			}
			
			$template_path = '';
			$sub_path = str_replace('index','',substr(strtolower($this->object),1));
			if( !empty($this->path_to_object) ){ $template_path = DS . $this->path_to_object; }
			
			if( $this->path_to_object == $sub_path ){
				if( !empty($sub_path) ){ $sub_path = DS . $this->path_to_object; }
			} else {
				if( !empty($sub_path) ){ $sub_path = DS . $this->path_to_object . DS . $sub_path; }
			}
			
			// load the body view
			ob_start();
			if(file_exists(__SELF__ . 'views' . $sub_path . DS . "v" . ucwords($view) . '.phtml')){
				include(__SELF__ . 'views' . $sub_path . DS . "v" . ucwords($view) . '.phtml');
			} else if( file_exists(__SELF__ . 'views' . DS . "v" . ucwords($view) . '.phtml' ) ){

				include(__SELF__ . 'views' . DS . "v" . ucwords($view) . '.phtml');
			} else {
				echo "Could not find file: ".__SELF__ . 'views' . $sub_path . DS . "v" . ucwords($view) . '.phtml';
			}
			$this->body = ob_get_clean();
			
			if( $this->getContentType() == "text/html" ){
				if(file_exists(__SELF__ . 'views' . $sub_path . DS . 'vNav.phtml')){
					ob_start();
					include(__SELF__ . 'views' . $sub_path . DS . 'vNav.phtml');
					$this->nav = ob_get_clean();
				}
	
				if( file_exists(__SELF__ . 'views' . $sub_path . DS . 'vTemplate.phtml') ){
					ob_start();
					include(__SELF__ . 'views' . $sub_path . DS . 'vTemplate.phtml');
					$this->body = ob_get_clean();
				}
				
			} else { $loadTemplate = FALSE; }
			
			if( $loadTemplate ){
				// load the template
				ob_start();
				include(__OBRAY_SITE_ROOT__ . DS . 'themes' . DS . $this->template . '.phtml');
				$this->html = ob_get_clean();
				
			} else {
				$this->html = $this->body;

			} 
		}

		protected function css( $css ){
			$css = explode(',',$css);
			$this->css_array = array_merge($this->css_array,$css);
			$this->set("css",$this->css_array);
		}

		protected function js( $js ){
			$js = explode(',',$js);
			$this->js_array = array_merge($this->js_array,$js);
			$this->set("js",$this->js_array);
		}

	}?>