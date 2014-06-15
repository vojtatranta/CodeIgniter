<?php


require 'forms/autoload.php';

use Nette\Forms\Form;

class CI_form extends Form {
	
	private $action;
	private $action_params = array();
	private $mother_method;
	private $expected_fields = array();
	private $ci;
	private $load_jquery = false;
	private $load_jquery_validate = false;
	private $language;
	private $values;

	private $default_values = array();
	private $defaults_array_name = 'default_values';
	private $was_filled = false;
	private $default_rules = array('xss_clean');

	private $csrf_protect_field = 'csrf_protect';
	private $add_csrf_protection = false;

	public $form_id = '';
	public $form_class = 'ci-form ';
	public $components = array();
	public $label_postfix = '';
	public $label_postfix_text = ':';
	public $field_id_postfix = '-field';



	public function __construct( )
	{
		$this->ci = &get_instance();
		
		$this->ci->load->library('session');
		$this->ci->load->library('form_validation');
		$this->ci->load->library('input');
		$this->ci->load->library('uri');
		$this->ci->load->helper('url');

	}


	public function create_form(  )
	{
		$classname = __CLASS__;
		return new $classname;
	}


	public function create_input( $type, $name, $readable = '' )
	{
		$new_component = new StdClass();

		$new_component->tag = 'input';
		$new_component->readable = $readable;
		$new_component->attributes['type'] = $type;
		$new_component->attributes['name'] = $name;
		$new_component->label = $readable;
		$new_component->omit = false;


		$this->components[] = $new_component;

		return $new_component;
	}


	public function create_textarea( $name, $readable )
	{
		$new_component = new StdClass();

		$new_component->tag  = 'textarea';
		$new_component->readable  = $readable;
		$new_component->attributes['name'] = $name;
		$new_component->label = $readable;
		$new_component->omit = false;


		$this->components[] = $new_component;

		return $new_component;
	}

	public function create_select( $name, $keys_vals, $readable, $value_field_name  = ''  )
	{
		$new_component = new StdClass();

		$new_component->tag = 'select';
		$new_component->readable = $readable;
		$new_component->attributes['name'] = $name;
		$new_component->keys_vals = $keys_vals;
		$new_component->value_field_name = $value_field_name;
		$new_component->omit = false;
		$new_component->label = $readable;

		$this->components[] = $new_component;

		return $new_component;
	}

	public function create_multiple_select( $name, $keys_vals, $readable, $value_field_name  = ''  )
	{
		$new_component = new StdClass();

		$new_component->tag = 'select';
		$new_component->readable = $readable;
		$new_component->attributes['name'] = $name;
		$new_component->attributes['multiple'] = 'multiple';
		$new_component->keys_vals = $keys_vals;
		$new_component->value_field_name = $value_field_name;
		$new_component->omit = false;
		$new_component->label = $readable;

		$this->components[] = $new_component;

		return $new_component;
	}

	public function create_element( $tag, $classes = array(), $attrs = array(), $html = '' )
	{
		$new_component = new StdClass();

		$new_component->tag = $tag;
		$new_component->attributes = $attrs;
		$new_component->classes = $classes;
		if ( !empty($html)) $new_component->html = $html;


		$this->components[] = $new_component;

		return $new_component;

	}

	public function end_element( $element )
	{
		$component = new StdClass();

		$component->tag = "/$element->tag";

		$this->components[] = $component;

		return $component;
	}

	private function assemble_component( $component )
	{
		$out = $this->assemble_tag_start( $component );		

			$out .= $this->add_attributes( $component );

			$out .= $this->add_classes( $component );
						
			if ( !strpos($component->tag, '/') )
			{
				if ( isset($component->html) )
				{
					$out .= " >".$component->html; 
				}
				else
				{
					$out .= ' >';
				}
				
			} 
			
			return $out;
	}

	private function assemble_input( $component )
	{
		$out = $this->assemble_tag_start( $component );		


		$out .= $this->add_attributes( $component );

		$out .= $this->add_classes( $component );
					
		if ( !strpos($component->tag, '/') ) $out .= ' >';
		
		return $out;
	}

	private function assemble_textarea( $component )
	{
		$out = $this->assemble_tag_start( $component );

			foreach( $component->attributes as $key => $attr )
			{
				if ( $key != 'value' ) $out .= " $key=\"$attr\"";
			}

			$out .= $this->add_classes($component);
		
		if ( isset($component->attributes['value']) AND !empty($component->attributes['value']) )
		{
			$value = $component->attributes['value'];
			$out .= ">$value</textarea>";
		}
		else  	
		{
			$out .= '></textarea>';
		}										   										

		
		return $out;
	}

	private function assemble_select( $component )
	{
		$out = $this->assemble_tag_start( $component );

			$out .= $this->add_attributes( $component );
			$out .= $this->add_classes( $component );

			$out .= '>';

			$selected = '';
			if ( !empty($component->attributes['value']) ) $selected = $component->attributes['value'];

			foreach ( $component->keys_vals as $key => $val )
			{
				$field_val = $component->value_field_name;
				$val = ( is_array($val) AND isset( $val[$field_val]) ) ? $val[$field_val] : $val; 
				$key = ( is_array($val) AND isset( $val['id']) ) ? $val['id'] : $key; 
				
				$val = ( is_object($val) AND isset( $val->$field_val) ) ? $val->$field_val : $val; 
				$key = ( is_object($val) AND isset( $val->id) ) ? $val->id : $key; 

				if ( $key != $selected ) $out .= "<option value=\"$key\">$val</option>";
				else                     $out .= "<option selected=\"selected\" value=\"$key\">$val</option>";

			}
			
		$out .= '</select>';

		return $out;

	}

	private function assemble_multiple_select( $component )
	{
		$out = $this->assemble_tag_start( $component );

			$out .= $this->add_attributes( $component );
			$out .= $this->add_classes( $component );

			$out .= '>';

			$selected = array();



			if ( !empty($component->attributes['value']) )
			{
				foreach($component->attributes['value'] as $val)
				{
					if ( is_array($val) )
					{
						$selected[$val['id']] = $val['id'];
					}
					else
					{
						$selected[$val->id] = $val->id;
					}
					
				}
			} 


			foreach ( $component->keys_vals as $key => $val )
			{
				if ( isset($selected[$key]) ) $out .= "<option selected=\"selected\" value=\"$key\">$val</option>";
				else                    	  $out .= "<option value=\"$key\">$val</option>"; 

			}
			
		$out .= '</select>';

		return $out;

	}

	private function assemble_tag_start( $component )
	{
		$id = ( isset($component->attributes['id'])) ? $component->attributes['id'] : '';

		if ( isset($component->label) AND !empty($component->label) AND !$this->is_submit($component) AND !(isset($component->attributes['type']) AND $component->attributes['type'] == 'hidden' ) )
		{	
			if ( $component->tag == 'input' AND $component->attributes['type'] == 'file' AND $component->attributes['value'] )
			{
				$val = $component->attributes['value'];

				$id = $component->attributes['name'].$this->field_id_postfix;
								
				$out = "<label for=\"$id\">$component->label$this->label_postfix_text ($val)</label>$this->label_postfix<$component->tag id=\"$id\"";
			}	
			else
			{
				if ( $this->is_checkbox($component) )
				{
					$id = $component->attributes['name'].'-'.$component->attributes['value'].$this->field_id_postfix;
				}	
				else
				{
					$id = $component->attributes['name'].$this->field_id_postfix;
				}

				$out = "<label for=\"$id\">$component->label$this->label_postfix_text</label>$this->label_postfix<$component->tag id=\"$id\"";
			}

			
		}
		else
		{
			$out = "<$component->tag ";
		}

		return $out;
	}

	private function is_submit( $component )
	{
		return (isset($component->attributes['type']) AND $component->attributes['type'] == 'submit'  );
	}

	private function is_checkbox( $component ) 
	{
		if ( isset($component->attributes['type']) )
		{
			return ($component->attributes['type'] == 'checkbox');
		}

		return false;
	}


	public function set_default_values( $defaults )
	{
		$this->was_filled = true;
		
		$this->default_values = $defaults;
	}

	private function gather_default_values()
	{
		$defaults = array();
		$session_defaults = $this->ci->session->flashdata( $this->defaults_array_name );

		if ( !empty($session_defaults) ) $defaults = $this->default_values; //can be overriden by bellow statement

		if ( !empty($this->default_values) ) $defaults = $this->default_values;

		foreach( $this->components as $component )
		{
			if ( isset($component->attributes['name']) )
			{
				$comp_name = $component->attributes['name'];

				if ( isset( $defaults[$comp_name] ) AND !isset($component->attributes['value'])  )
				{
					$component->attributes['value'] = $defaults[$comp_name];
				}
			}
			
		}
		
	}

	public function get_component_by_name( $name )
	{
		foreach( $this->components as &$component )
		{
			if ( isset($component->attributes['name']) && $component->attributes['name'] == $name ) return $component;
		}
	}

	public function enable_csrf_protect()
	{
		$this->add_csrf_protection = true;
	}

	private function is_form_in_post()
	{		
		$post = $this->ci->input->post();
		if ( !empty($post) )
		{
			
			if ( $this->add_csrf_protection AND !isset($post[$this->csrf_protect_field]) ) 
			{
				return false;
			}

			if ( count($post) > count($this->expected_fields) ) return false;

			foreach ($this->expected_fields as $name) 
			{
				$component = $this->get_component_by_name( $name );
				if ( !isset($post[$name]) AND !$component->omit AND !$this->is_checkbox($component)) 
				{
					return false;
				}
			}

			return true;
		}
		return false;
	}

	private function check_csrf( )
	{
		$post = $this->ci->input->post();
		if ( $this->ci->session->userdata($this->csrf_protect_field) != $post[$this->csrf_protect_field] )
		{
			throw new Exception("Invalid CSRF protection", 1);
		}

		$this->ci->session->unset_userdata($this->csrf_protect_field);

		return true;
	}

	private function validate_form()
	{
		foreach( $this->components as $component )
		{
			if ( !empty($component->attributes['name']) AND isset($component->rules) )
			{
				$name = $component->attributes['name'];
				
				$this->ci->form_validation->set_rules( $name, $component->readable, implode('|', array_merge($component->rules, $this->default_rules)) );
			}
			else if ( !empty($component->attributes['name']) )
			{
				$name = $component->attributes['name'];
				$this->ci->form_validation->set_rules($name, $component->readable, implode('|', $this->default_rules));
			}
		}



		$this->ci->form_validation->run();

		return validation_errors();
	}

	private function gather_expected_fields()
	{
		foreach( $this->components as $component )
		{
			if ( isset($component->attributes['name']) AND !empty($component->attributes['name']) )
			{
				$this->expected_fields[] = $component->attributes['name'];
			}
		}
	}

	public function assemble()
	{
		if ( !isset($this->form_action) )
		{
			throw new Exception('Action must be set!!First call create_form($action, $action_params)', 1);
		}


		//added using JS for antispam
		$this->csrf_protect = md5(rand(214,5564)+rand(5465,4564));

		if ( $this->add_csrf_protection )
		{
			$csrf_field = $this->create_input('hidden', $this->csrf_protect_field, 'csfr');
		}

		$this->gather_expected_fields();
		$this->gather_default_values();

		
		$base_url = base_url();
 		

		$out = "<form action=\"$this->form_action\" data-csrf_protect=\"$this->csrf_protect\" data-base-url=\"$base_url\" class=\"$this->form_class\" enctype=\"multipart/form-data\" id=\"$this->form_id\" method=\"post\">";
		

		foreach( $this->components as $comp )
		{
			if ( isset($comp->attributes['multiple']) )
			{
				$method_name = 'assemble_multiple_'.$comp->tag;
			}
			else
			{
				$method_name = 'assemble_'.$comp->tag;
			}
			


			if ( method_exists($this, $method_name) )
			{
				$out .= $this->$method_name( $comp );
			}
			else
			{
				$out .= $this->assemble_component( $comp );
			}

			if ( !empty($comp->attributes['name']) ) $this->field_names[] = $comp->attributes['name'];
		}

		$out .= '</form>';

		


		$this->ci->session->set_userdata(array( $this->csrf_protect_field => $this->csrf_protect ));


		$out .= $this->add_js( $out );
		
		$this->assembled = $out;

		return $this->assembled;
	}

	public function process_values( $post )
	{
		$post = array_merge($post, $_FILES);
		$fin = array();

		//handle checkboxes
	 	foreach( $this->components as $component )
	 	{
	 		if ( isset($component->attributes['name']) AND !empty($component->attributes['name']) )
	 		{
	 			$dirty_name = $component->attributes['name'];
	 			if ( strstr($dirty_name, '[') AND strstr($dirty_name, ']')  ) 
	 			{
	 				$dirty_name = explode('[', $dirty_name);
	 				$comp_name = $dirty_name[0];
	 			}
	 			else
	 			{
	 				$comp_name = $component->attributes['name'];
	 			}
	 				

	 			if ( isset($component->attributes['type'])  AND $component->attributes['type'] == 'checkbox' )
		 		{
		 			if ( isset($post[$comp_name])) $fin[$comp_name] = 1;
		 			else                           $fin[$comp_name] = 0;
		 		}
		 		else if ( $comp_name == $this->csrf_protect_field )
		 		{
		 			unset($component);
		 		}
		 		else
		 		{
		 			if ( !isset($component->attributes['disabled']) )
		 			{
		 				$fin[$comp_name] = $post[$comp_name];
		 			} 
		 		}
	 		}
	 		
	  	}

	  	return $fin;
	}

	public function validate_with($data)
	{		
		$post = $data;
		$files = $_FILES;
		if ( !empty($post) OR !empty($files) )
		{
			$validation_errors = $this->validate_form();

			if ( empty($validation_errors) )
			{
				$this->save_values();

				return true;
			}
			else
			{		
				$post  = $this->ci->input->post();
				$default_values = $this->remove_passwords( $this->ci->input->post() );

				$this->ci->session->set_flashdata( $this->defaults_array_name, $default_values );

				$this->ci->session->set_flashdata('form_errors', $validation_errors); 

				$this->validation_errors = $validation_errors;

				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public function validate_post()
	{		
		$post = $this->ci->input->post();
		$files = $_FILES;
		if ( !empty($post) OR !empty($files) )
		{
			$validation_errors = $this->validate_form();

			if ( empty($validation_errors) )
			{
				$this->save_values();

				return true;
			}
			else
			{		
				$post  = $this->ci->input->post();
				$default_values = $this->remove_passwords( $this->ci->input->post() );

				$this->ci->session->set_flashdata( $this->defaults_array_name, $default_values );

				$this->ci->session->set_flashdata('form_errors', $validation_errors); 

				$this->validation_errors = $validation_errors;

				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public function get_errors()
	{
			return isset($this->validation_errors) ? $this->validation_errors : false;
	}

	private function save_values()
	{
		$this->values = array_merge($this->process_values($this->ci->input->post()), $_FILES);
	}

	private function params_to_array( $str )
	{
		$fin = array();
		foreach( explode('/', $str) as $param )
		{
			if ( !empty($param) ) $fin[] = $param;
		}

		return $fin;
	}

	public function get_values()
	{
		return $this->values;
	}

	private function serialize_self()
	{
		$this->ci->session->set_flashdata( $this->form_id, serialize($this->values));
	}

	public function unserialize_form( $form_id )
	{
		return unserialize( $this->ci->session->flashdata($form_id) );
	}


	public function render()
	{
		if ( isset($this->assembled) )
		{
			echo $this->assembled;
		}
		else
		{
			echo $this->assemble();
		}
		
	}

	private function add_attributes( $component )
	{
		$out = '';


		if ( empty( $component->attributes ) ) return $out;

		foreach( $component->attributes as $key => $attr )
		{
			if ( is_array($attr) ) continue;
			$out .= " $key=\"$attr\"";
		}

		return $out;
	}

	private function add_classes( $component )
	{
		$out = '';

		if ( !empty($component->type) ) $component->classes[] = $component->type;

		if ( !empty($component->rules) ) 
		{
			foreach( $component->rules as $rule)
			{
				$component->classes[] = $rule;
			} 
		}


		if ( empty($component->classes) ) return $out;
		
			$out = " class=\"";

			$classes = $component->classes;

			if ( is_array($classes) )
			{
				$out .= implode(" ", $classes);
			}
			else
			{
				$out.= $classes;
			}

			$out .="\"";
			
			if ( isset($component->rules) AND in_array('required', $component->rules) ) $out .= " required";

		return $out;
	}


	private function remove_passwords( $arr )
	{
		$fin = array();
		foreach( $this->components as $component )
		{
			$comp_name = $component;

			if ( isset($arr[ $comp_name ]) AND $component->attributes['type'] != 'password' )
			{
				$fin[ $comp_name ] = $arr[ $comp_name ];
			}
		}

		return $fin;
	}

	public function load_jquery()
	{
		$this->load_jquery = true;
	}

	public function disable_js_validate()
	{
		$this->load_jquery_validate = false;
	}

	private function add_js( )
	{
		$js_used = false;
		foreach( $this->components as $component )
		{
			if ( !empty($component->rules)  ) $js_used = true;
		}

		if (!$js_used) return '';

		$out = '';
		$js = array();

		$js_path = 'assets/js/';
		$js_url  = base_url().$js_path;


		if ( $this->load_jquery )          $js['jQuery'] = $js_url.'lib/jquery-1.8.3.js';
		if ( $this->load_jquery_validate ) $js['jQuery_validate'] = $js_url.'dist/jquery.validate.min.js';

		$js_tag = "<script type=\"text/javascript\" src=\"{src}\"></script>";

		foreach( $js as $repo )
		{
			$out .= str_replace('{src}', $repo, $js_tag);
		}

		


		if ( $this->load_jquery_validate )
		{	
			$localization = 'messages_'.$this->language.'.js';

			if ( file_exists( './'.$js_path.'/localization/'.$localization ) )
			{
				$out .= str_replace('{src}', $js_url.'localization/'.$localization, $js_tag);
			}

			$out .= "<script>";
				$out .= "$('#$this->form_id').validate();";
				
			

		}

			if ( $this->add_csrf_protection ) $out .= "$('[name=$this->csrf_protect_field').val($('#$this->form_id').data('$this->csrf_protect_field'));";
			$out .= "</script>";

		return $out;
		
	}
	
	public function automate_element( $element, $config )
	{
		if ( isset($config[$element->attributes['name']]) )
		{
			$config = $config[$element->attributes['name']];
			
			if ( $config['tag'] == 'select' )
			{
				$keys_vals = array();
				$res = $this->ci->db->get( $config['table'] )->result_array();

				foreach( $res as $row )
				{
					$keys_vals[$row['id']] = $row[$config['readable']];
				}

				$element->tag = $config['tag'];
				$element->keys_vals = $keys_vals;
				$element->label = $element->attributes['name'];
				$element->value_field_name = $config['readable']; //this readable indicates which column wil be shown as <option> text
			}
			else if ( $config['tag'] == 'textarea' ) 
			{
				$element->tag = $config['tag'];
			}
		}
		return $element;
	}


	public function set_language( $lang )
	{
		if ( strlen($lang) > 2 )
		{
			$lang = $this->get_lang_code( $lang );
		}

		$this->language = $lang;
	}



	public function get_lang_code( $lang )
	{
		

		$language_codes = array(
			 "aa" => "Afar",
			 "ab" => "Abkhazian",
			 "ae" => "Avestan",
			 "af" => "Afrikaans",
			 "ak" => "Akan",
			 "am" => "Amharic",
			 "an" => "Aragonese",
			 "ar" => "Arabic",
			 "as" => "Assamese",
			 "av" => "Avaric",
			 "ay" => "Aymara",
			 "az" => "Azerbaijani",
			 "ba" => "Bashkir",
			 "be" => "Belarusian",
			 "bg" => "Bulgarian",
			 "bh" => "Bihari",
			 "bi" => "Bislama",
			 "bm" => "Bambara",
			 "bn" => "Bengali",
			 "bo" => "Tibetan",
			 "br" => "Breton",
			 "bs" => "Bosnian",
			 "ca" => "Catalan",
			 "ce" => "Chechen",
			 "ch" => "Chamorro",
			 "co" => "Corsican",
			 "cr" => "Cree",
			 "cs" => "Czech",
			 "cu" => "Church Slavic",
			 "cv" => "Chuvash",
			 "cy" => "Welsh",
			 "da" => "Danish",
			 "de" => "German",
			 "dv" => "Divehi",
			 "dz" => "Dzongkha",
			 "ee" => "Ewe",
			 "el" => "Greek",
			 "en" => "English",
			 "eo" => "Esperanto",
			 "es" => "Spanish",
			 "et" => "Estonian",
			 "eu" => "Basque",
			 "fa" => "Persian",
			 "ff" => "Fulah",
			 "fi" => "Finnish",
			 "fj" => "Fijian",
			 "fo" => "Faroese",
			 "fr" => "French",
			 "fy" => "Western Frisian",
			 "ga" => "Irish",
			 "gd" => "Scottish Gaelic",
			 "gl" => "Galician",
			 "gn" => "Guarani",
			 "gu" => "Gujarati",
			 "gv" => "Manx",
			 "ha" => "Hausa",
			 "he" => "Hebrew",
			 "hi" => "Hindi",
			 "ho" => "Hiri Motu",
			 "hr" => "Croatian",
			 "ht" => "Haitian",
			 "hu" => "Hungarian",
			 "hy" => "Armenian",
			 "hz" => "Herero",
			 "ia" => "Interlingua (International Auxiliary Language Association)",
			 "id" => "Indonesian",
			 "ie" => "Interlingue",
			 "ig" => "Igbo",
			 "ii" => "Sichuan Yi",
			 "ik" => "Inupiaq",
			 "io" => "Ido",
			 "is" => "Icelandic",
			 "it" => "Italian",
			 "iu" => "Inuktitut",
			 "ja" => "Japanese",
			 "jv" => "Javanese",
			 "ka" => "Georgian",
			 "kg" => "Kongo",
			 "ki" => "Kikuyu",
			 "kj" => "Kwanyama",
			 "kk" => "Kazakh",
			 "kl" => "Kalaallisut",
			 "km" => "Khmer",
			 "kn" => "Kannada",
			 "ko" => "Korean",
			 "kr" => "Kanuri",
			 "ks" => "Kashmiri",
			 "ku" => "Kurdish",
			 "kv" => "Komi",
			 "kw" => "Cornish",
			 "ky" => "Kirghiz",
			 "la" => "Latin",
			 "lb" => "Luxembourgish",
			 "lg" => "Ganda",
			 "li" => "Limburgish",
			 "ln" => "Lingala",
			 "lo" => "Lao",
			 "lt" => "Lithuanian",
			 "lu" => "Luba-Katanga",
			 "lv" => "Latvian",
			 "mg" => "Malagasy",
			 "mh" => "Marshallese",
			 "mi" => "Maori",
			 "mk" => "Macedonian",
			 "ml" => "Malayalam",
			 "mn" => "Mongolian",
			 "mr" => "Marathi",
			 "ms" => "Malay",
			 "mt" => "Maltese",
			 "my" => "Burmese",
			 "na" => "Nauru",
			 "nb" => "Norwegian Bokmal",
			 "nd" => "North Ndebele",
			 "ne" => "Nepali",
			 "ng" => "Ndonga",
			 "nl" => "Dutch",
			 "nn" => "Norwegian Nynorsk",
			 "no" => "Norwegian",
			 "nr" => "South Ndebele",
			 "nv" => "Navajo",
			 "ny" => "Chichewa",
			 "oc" => "Occitan",
			 "oj" => "Ojibwa",
			 "om" => "Oromo",
			 "or" => "Oriya",
			 "os" => "Ossetian",
			 "pa" => "Panjabi",
			 "pi" => "Pali",
			 "pl" => "Polish",
			 "ps" => "Pashto",
			 "pt" => "Portuguese",
			 "qu" => "Quechua",
			 "rm" => "Raeto-Romance",
			 "rn" => "Kirundi",
			 "ro" => "Romanian",
			 "ru" => "Russian",
			 "rw" => "Kinyarwanda",
			 "sa" => "Sanskrit",
			 "sc" => "Sardinian",
			 "sd" => "Sindhi",
			 "se" => "Northern Sami",
			 "sg" => "Sango",
			 "si" => "Sinhala",
			 "sk" => "Slovak",
			 "sl" => "Slovenian",
			 "sm" => "Samoan",
			 "sn" => "Shona",
			 "so" => "Somali",
			 "sq" => "Albanian",
			 "sr" => "Serbian",
			 "ss" => "Swati",
			 "st" => "Southern Sotho",
			 "su" => "Sundanese",
			 "sv" => "Swedish",
			 "sw" => "Swahili",
			 "ta" => "Tamil",
			 "te" => "Telugu",
			 "tg" => "Tajik",
			 "th" => "Thai",
			 "ti" => "Tigrinya",
			 "tk" => "Turkmen",
			 "tl" => "Tagalog",
			 "tn" => "Tswana",
			 "to" => "Tonga",
			 "tr" => "Turkish",
			 "ts" => "Tsonga",
			 "tt" => "Tatar",
			 "tw" => "Twi",
			 "ty" => "Tahitian",
			 "ug" => "Uighur",
			 "uk" => "Ukrainian",
			 "ur" => "Urdu",
			 "uz" => "Uzbek",
			 "ve" => "Venda",
			 "vi" => "Vietnamese",
			 "vo" => "Volapuk",
			 "wa" => "Walloon",
			 "wo" => "Wolof",
			 "xh" => "Xhosa",
			 "yi" => "Yiddish",
			 "yo" => "Yoruba",
			 "za" => "Zhuang",
			 "zh" => "Chinese",
			 "zu" => "Zulu"
			);

			if ( strlen($lang) != 2 )
			{
				$language_codes = array_flip( $language_codes );
			}

			return $language_codes[ $lang ];
	}

}


