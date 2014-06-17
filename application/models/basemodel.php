<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Basemodel extends CI_Model {

	protected $db_query;

	public function __construct()
	{
		parent::__construct();

	}

	/**
	 * Returns first public property of instance
	 * @return string string that describes class
	 */
	public function __toString(  )
	{
		$inst_class = self::model_to_table_name($this);

		foreach($this->ci_get($inst_class) as $prop => $val) //we do not want get properties in private context		
		{
			return $prop;
		}
	}

	public function is_loaded($class_name)
	{
		$ci =& get_instance();

		return isset($ci->$class_name);
	}

	public function ci_get($class_name)
	{
		$ci =& get_instance();
		
		return $ci->$class_name;
	}

	/**
	 * This methods creates list of object properties that do not reflect a table column and dispatches them for creation
	 * @return void
	 */
	protected function sync_db()
	{
		$class_name = self::model_to_table_name($this);

		if (isset($this->$class_name)) return true; //check for DB structure changes only when this instance is the first in app

		$table_name = self::model_to_table_name( $this );


		if ( !$this->db->table_exists( $table_name ) )
		{
			$this->create_model_table( $this );
		}

		$db_columns = $this->db->list_fields( $table_name );

		$model_props = $this->get_fields_to_create( $this );

		$flipped = array_flip($db_columns);
		$to_create = array();
		foreach ($model_props as $prop) 
		{
			if (!isset($flipped[$prop])) $to_create[] = $prop;
		}

		
		$this->alter_table( $to_create );
	}	

	public static function syncdb($class_name)
	{
		$ci =& get_instance();

		$class_reflection = new ReflectionClass($class_name);


		if (!class_exists($class_name) OR !$class_reflection->isSublcassOf(__CLASS__))
		{
			throw new Exception("Given class does not exists or is not a model class", 1);	
		}

		$fields_settings = $class_name::$fields_settings;

		if (!$ci->db->table_exists($class_name))
		{
			self::_static_create_model_table($class_name);
		}

		$existing_columns = array_flip($ci->db->list_fields($class_name));

		$models = array_flip(self::static_get_models());



		$new_columns = array();

		foreach($fields_settings as $prop => $settings)
		{	
			$reflection_property = new ReflectionProperty($class_name, $prop);

			if (!$class_reflection->hasProperty($prop) OR isset($existing_columns[$prop]) OR !$reflection_property->isPublic())
			{
				continue;
			} 

			$rules = isset($settings['rules']) ? array_flip($settings['rules']) : array();

			$type = isset($settings['type']) ? strtoupper($settings['type']) : 'VARCHAR';

			if (isset($models[$type]))
			{
				$new_column = array('type' => 'INT');
			}
			else
			{
				$new_column = array('type' => $type);
			}

			if (!isset($rules['required'])) 
			{
				$new_column['null'] = True;
			}

			if (isset($rules['unique']))
			{
				$new_column['index'] = 'UNIQUE';
			}

			$new_column['multiple'] = isset($settings['multiple']);

			$new_columns[$prop] = $new_column;
  
		}

		if (empty($new_columns)) return true;

		$ci->load->dbforge();

		foreach ($new_columns as $name => $settings) 
		{
			if ($settings['multiple'])
			{
				self::_add_multiple_field($class_name, $name, $settings);
				continue;
			}

			$new_fields[$name] = array('type' => $settings['type'], 'null' => isset($settings['null']));	

			if ($settings['type'] == 'VARCHAR')
			{
				$new_fields['name']['constraint'] = 255; 
			}

		}

		$ci->dbforge->add_column( $class_name, $new_fields );	
	}

	private static function _add_multiple_field($class_name, $field_name, $settings)
	{
		$ci =& get_instance();

		$conn_table_name = $class_name.'_'.$settings['type'];

		$tables = array_flip($ci->db->list_tables());

		if ( !isset($tables[$conn_table_name]) )	
		{	
			$conn_fields[] = array( $conn_table_name => array('TYPE' => 'int') );
			$conn_fields[] = array( $settings['type'] => array('TYPE' => 'int', 'NULL' => true) );

				$this->dbforge->add_field('id');
				$this->dbforge->add_field($conn_fields);

			$this->dbforge->create_table( $conn_table_name );
		}	
	}

	protected function _sync_db()
	{
		$class_name = self::model_to_table_name($this);

		if ($this->is_loaded($class_name)) return true; //this is not first instance of $this class

		return self::syncdb($class_name);

		$table_name = self::model_to_table_name( $this );


		if ( !$this->db->table_exists( $table_name ) )
		{
			$this->_create_model_table( $this );
		}

		$db_columns = $this->db->list_fields( $table_name );

		$model_props = $this->get_fields_to_create( $this );

		$flipped = array_flip($db_columns);
		$to_create = array();
		foreach ($model_props as $prop) 
		{
			if (!isset($flipped[$prop])) $to_create[] = $prop;
		}

		
		$this->alter_table( $to_create );
	}	

	/**
	 * Gets class of instance given in parameter and returns it in lowercase
	 * @param  mixed $model instance of model class
	 * @return string classname  classname in lowercase
	 */
	public function model_to_table_name( $model )
	{
		return strtolower(get_class($model));
	}

	public function is_public($prop)
	{
		try {
			$reflection = new ReflectionProperty(get_class($this), $prop);
		} catch (Exception $e) {
			return true;
		}
		
		return $reflection->isPublic();
	}

	public function get_table_structure()
	{
		$model_table = self::model_to_table_name($this);

		return $this->db->query("DESCRIBE $model_table")->result_array();
	}

	protected function alter_table( $fields )
	{
		$this->load->dbforge();
		$model_table = self::model_to_table_name($this);

		$table_columns = array_flip($this->db->list_fields( $model_table ));
		$model_props = $this->object_props_with_types( $this );

		foreach($fields as $prop)
		{
			$new_field = array();
			if ( !$this->is_public($prop) ) continue;

			if ( !is_array($prop) AND !isset($table_columns[$prop]) )
			{
				$new_field[$prop] = array('type' => $this->get_db_type($this->$prop), 'null' => true);	
			
				$this->dbforge->add_column( $model_table, $new_field );				
			}

					
			if ( $this->is_multiple($prop) )
			{
				$prop = $this->get_field_settings($prop);
				$conn_table_name = $model_table.'_'.$prop['type'];

				$tables = array_flip($this->db->list_tables());

				if ( !isset($tables[$conn_table_name]) )	
				{	
					$id = array( 'id' => array('TYPE' => 'int', 'AUTO_INCREMENT' => true) );
					
					$first_conn =  array( $conn_table_name => array('TYPE' => 'int') );
					$second_conn = array( $prop['type'] => array('TYPE' => 'int', 'NULL' => true) );

						$this->dbforge->add_field($id);
						$this->dbforge->add_field($first_conn);
						$this->dbforge->add_field($second_conn);

					$this->dbforge->add_key('id', true);
					$this->dbforge->create_table( $conn_table_name );
				}			
			}
		}
	}

	private function _create_model_table( $model )
	{
		$this->load->dbforge();

		$table_name = self::model_to_table_name( $model );
		$this->dbforge->add_field('id');
		$this->dbforge->create_table( $table_name );
	}

	private static function _static_create_model_table( $class_name )
	{
		$ci =& get_instance();
		$ci->load->dbforge();

		$ci->dbforge->add_field('id');
		$ci->dbforge->create_table( $class_name );
	}


	protected function get_db_type( $var )
	{
		$type = gettype($var);
		$translate = array(
				'string' => 'text',	
				'integer' => 'int',
			);

		return (isset($translate[$type])) ? $translate[$type] : 'text';
	}

	public function get_object_props( $object )
	{
		$fin = array();
		$fields_settings = $object->get_field_settings();

		foreach( $object as $k => $prop)
		{
			if ( !is_array($prop)) 
			{
				$fin[] = $k;
			}
		}

		return $fin;
	}

	public function get_fields_to_create( $object )
	{
		$fin = array();
		foreach( $object as $prop => $val )
		{
			if ( !$this->is_public($prop) ) continue;

			$fin[] = $prop;
		}

		return $fin;
	}

	public function object_props_with_types($object)
	{
		$fin = array();
		$fields_settings = $object->get_field_settings();

		foreach( $object as $prop => $val)
		{
			if ( !$this->is_public($prop) ) continue;

			
			if ( !isset($fields_settings[$prop]) )
			{
				$fin[] = $prop;
			}
			else
			{
				$field_settings = $fields_settings[$prop];
				if ( isset($field_settings['widget']) AND $field_settings['widget'] == 'multiple' )
				{
					$fin[] = array('type' => $field_settings['type'], 'widget' => 'multiple');
				}
				else
				{
					$fin[] = $prop;
				}
			}
				
			
		}

		return $fin;
	}

	public static function static_get_models()
	{
		$models = array();
		$ci =& get_instance();
		$tables = $ci->db->list_tables();

		foreach( $tables as $table_name )
		{
			$entries = array_flip(scandir(APPPATH.DIRECTORY_SEPARATOR.'models'));

			if ( isset($entries[$table_name.'.php']) ) $models[] = $table_name;
		
		}

		return $models;
	
	}

	public static function get_models()
	{
		$models = array();
		$tables = $this->db->list_tables();

		foreach( $tables as $table_name )
		{
			$entries = array_flip(scandir(APPPATH.DIRECTORY_SEPARATOR.'models'));

			if ( isset($entries[$table_name.'.php']) ) $models[] = $table_name;
		
		}

		return $models;
	
	}

	public function is_model( $class_name )
	{
		$models = array_flip($this->get_models());

		return isset($models[$class_name]);
	}


	public function get_paging($offset = 25)
	{
		$q = $this->get();
		$this_table = self::model_to_table_name($this);
		$count = $this->db_query->get($this_table)->num_rows();

		$pages = array();
		for($i = 1; $i <= floor($count / $offset); $i++)
		{
			if ($i == 1)
			{
				$pages[''] = $i;
			}
			else
			{
				$pages[$i] = $i;
			}
		}

		return $pages;
	}

	public function get_instances($limit = null, $offset = null, $order = 'desc', $order_by_field = 'id')
	{
		$table_name = self::model_to_table_name($this);

		$query_res = $this->db->order_by($order_by_field, $order);

		$query_res = $query_res->get($table_name, $limit, $offset)->result( $table_name );
	
			//echo $this->db->last_query().'<br>';

		return $query_res;
	}



	public function save( $props = null)
	{
		if ( method_exists($this, 'hook_presave') )
		{
			$this->hook_presave();
		}

		$table_name = self::model_to_table_name($this);

		if ( !is_null($props) )
		{
			foreach( $props as $prop => $val )
			{
				$this->$prop = (empty($val)) ? null : $val;
			}
		}


		$m_to_n_scheme = array();
		$save_arr = array();

		foreach($this as $prop => $val)
		{

			$field_settings = $this->get_field_settings($prop);

			if ( !$this->is_public($prop) ) continue;

			if ( is_array($val) AND isset($val['size']) AND isset($val['tmp_name']) ) //yes this is a file ready to uplaod
			{
				if ( $val['error'] > 0 ) continue; //if file is empty than omit

				if ( isset($field_settings['upload_path']) )
				{
					$upload_path = $field_settings['upload_path'];
				}
				else
				{
					$upload_path = './';
				}

				$val = $this->upload($prop, $upload_path);
			}

			if ( $this->is_set_as_type( $prop ) AND !$this->is_multiple($prop) ) 
			{
				if ( is_object($val) )
				{
					$save_arr[$prop] = (empty($val)) ? null : $val->id;
				}
				elseif (is_array($val)) 
				{
					$save_arr[$prop] = (empty($val)) ? null : $val['id'];
				}
				else
				{
					$save_arr[$prop] = (empty($val)) ? null : $val;
				}
			}
			else
			{
				if ( $this->is_multiple($prop) ) //M:N relation save now
				{		
					$field_settings['field'] =  $prop;
					$m_to_n_scheme[] = $field_settings;
				}
				else
				{	
					$save_arr[$prop] = (empty($val) AND $val != '0') ? null : $val;

				}
				
			}
		}

		if ( method_exists($this, 'hook_save') )
		{
			$save_arr = $this->hook_save($save_arr);
		}

		$update_only = false;
		if ( isset($save_arr['id']) )
		{
			$id = $save_arr['id'];
			unset($save_arr['id']);

			$this->db->where('id', $id)->update($table_name, $save_arr);
			$res = $this->db->where('id', $id)->get($table_name)->row(0, $table_name);
			$update_only = true;;
		}
		else
		{
			$this->db->insert($table_name, $save_arr);
			$res = $this->db->where('id', $this->db->insert_id())->get($table_name)->row(0, $table_name);

			$id = $res->id;
		}

		foreach($m_to_n_scheme as $scheme)
		{
			$connection_table = $this->create_connection_table_name( $table_name, $scheme['type'] );

			if ( $update_only ) //if we are updating, I delete all old connections I KNOW, IT IS DANGEROUS BECAUSE OF LOOSING DATA
			{
				$this->db->where($table_name, $id)->delete( $connection_table );
			}

			$multiple_filed = $scheme['field'];
			$i = 0;

			foreach( $this->$multiple_filed as $relation_member )
			{
				if (!empty($relation_member))
				{
					$save_arr = array( $table_name => $id, $scheme['type'] => $relation_member );
					$this->db->insert($connection_table, $save_arr);

					$multi = &$this->$multiple_filed;

					$multi[$i] = (empty($relation_member)) ? null : $relation_member;	
				}
												
				
				$i++;
			}	
		}

		

		$this->id = $id;


		return $this;
	}

	protected function create_connection_table_name( $base_table, $target_table )
	{
		if ( is_object($base_table) )
		{
			$base_table = self::model_to_table_name($base_table);
		}

		return $base_table.'_'.$target_table;

	}

	public function init_props( $props )
	{
		if ( is_object($props) )
		{
			$std = new StdClass();
			if ( $props instanceof $std ) $std = true;
			else return $props;
		}
		$this_table_name = self::model_to_table_name( $this );
		$multiple_field = false;
		foreach ($this as $prop => $val) 
		{
			if ( $this->is_multiple($prop) ) $multiple_field = $prop;

			if ( is_array($props) )
			{
				if (isset($props[$prop])) $this->$prop = $props[$prop];
			}
			else
			{
				if (isset($props->$prop)) $this->$prop = $props->$prop;
			}
		}

		if ( is_array($props) )
		{
			if ( isset($props['id']) ) $this->id = $props['id'];
		}
		else
		{
			if ( isset($props->id) ) $this->id = $props->id;
		}
		
		if ( $multiple_field )
		{
			$type_of_multiple = $this->get_type_of($multiple_field);
			$connection_table = $this->create_connection_table_name( $this_table_name, $type_of_multiple );
			
			$prop_arr = array();
			$this->$multiple_field = $prop_arr;
			
			if ( empty($this->$multiple_field) )
			{
				$arr = array();

				if ( isset($props[$multiple_field]) )
				{
					foreach($props[$multiple_field] as $val)
					{
						$arr[] = $val;
					}

					$this->$multiple_field = $arr;
				} 
				else
				{
					$count = ($this->get_field_settings($multiple_field,'count')) ? $this->get_field_settings($multiple_field,'count') : 2;
					for($i = 0; $i < $count; $i++)
					{
						$arr[$i] = '';
					}

					$this->$multiple_field = $arr;
				}
				
			}
		}

		return $this;
	}

	public function _create_form($action = '.', $action_params = array())
	{
		//prer($this);
		$this->load->library('ci_form');

		$table_name = self::model_to_table_name( $this );

		$form = $this->ci_form->create_form( );
		$form->setAction($action.implode('/', $action_params));

		$class_name = self::model_to_table_name($this);

		$field_data = $this->get_field_data($class_name);

		if ( isset($this->field_settings) ) $fields_set = true;


		$class_name = self::model_to_table_name($this);

		foreach( $this as $field => $val )
		{
			if ( is_array($val) AND !$this->is_set_as_type($field) ) continue;

			$field_prefs = array('widget' => 'text', 'field_uni' => (string) $this, 'value' => $val, 'options' => array());
	
				
			if ( isset($field_data[$field]) )
			{
					switch ($field_data[$field]->type) {
					case 'text':
						$field_prefs['widget'] = 'text';
					break;
					
					case 'tinyint':
						$field_prefs['widget'] = 'checkbox';
					break;
					case 'int':
						$field_prefs['widget'] = 'number';
					break;
				}
			}

			//finding widgets
			
			if ( !$this->is_public($prop) ) continue;
			if ( class_exists($field) ) $field_prefs['widget'] = 'select';


			if ( $field_settings = $this->$class_name->get_field_settings() )
			{
				if ( isset($field_settings[$field]) )
				{
					$settings = $field_settings[$field];

					$field_prefs['value'] = (!empty($val)) ? (!empty($settings['value'])) ? $settings['value'] : $val : '';

					if ( is_object($field_prefs['value']) )
					{
						$field_prefs['value'] = $field_prefs['value']->id;
					}

					$field_prefs['options'] = (!empty($settings['options'])) ? $settings['options'] : $field_prefs['options'];

					if (isset( $settings['widget'])) $field_prefs['widget'] = $settings['widget'] ;
					if (isset($settings['count']))   $field_prefs['count'] = $settings['count'];
					else                             $field_prefs['count'] = 2;


					if ( isset($settings['type']) AND class_exists($settings['type']) AND (!isset($settings['widget']) OR !$this->is_multiple($field)) )
					{
						$field_prefs['widget'] = 'select';
						$table_name = $settings['type']; //equals class name
						$options = $this->db->get( $table_name )->result($table_name);


						foreach( $options as $option )
						{
							$field_prefs['options'][$option->id] = (string) $option;
						}
					}
				}
			}	

		
			$div = $form->create_element('div', 'field-wrap multiple-fields field-'.$field );	

			if ( $this->is_pk($field) AND !empty($val) ) 
			{
				$field = $form->create_input( 'hidden', $field, $val );
					$field->attributes['value'] = $val;

				$form->end_element( $div );
				continue;
			}


				switch($field_prefs['widget'])
				{
					case 'text':
						$field = $form->addText($field, t($field))->setDefaultValue($field_prefs['value']);
					break;
					case 'file':
						$field = $form->create_input( 'file', $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
					break;
					case 'hidden':
						$field = $form->create_input( 'hidden', $field, '' );
							$field->attributes['value'] = $field_prefs['value'];
					break;
					case 'select':


						$field_prefs['options'][null] = '----';

						if ( class_exists($field) OR $this->is_set_as_type($field) )
						{
							if ( $this->is_set_as_type($field) )
							{
								$type = $this->get_type_of($field);
							} 
							else
							{
								$type = $field;
							}

							$res = $this->db->get($type)->result($type);

							foreach($res as $row)
							{
								$field_prefs['options'][$row->id] = (string) $row;
							}

							if ( isset($res[0]) )
							{
								foreach($res[0] as $k => $arr )
								{
									if ( $k != 'id' ) $field_prefs['field_uni'] = $k;
								}
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}

							
						}
						else
						{
							$type = $field;
							$help_arr = $field_prefs['options'];
							$dummy = $help_arr[null];

							unset($help_arr[null]);
							
							$field_prefs['options'] = array();
							$field_prefs['options'][null] = $dummy;

							foreach ($help_arr as $k => $meal) 
							{
								$field_prefs['options'][$k] = $meal;
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
						}

						$options = array(null => '------');
						$options = $field_prefs['options'] + $options; 

						$select = $form->create_select( $type, $field_prefs['options'], t($field, 'admin'), $field_prefs['field_uni'] );
							$select->attributes['value'] = $field_value;
					break;
					case 'multiple':
						$field_prefs['options'][null] = '----';


						if ( $type = $this->is_class_or_type($this, $field) )
						{
							$res = $this->db->get($type)->result($type);

							foreach($res as $row)
							{
								$field_prefs['options'][$row->id] = (string) $row;
							}

							if ( isset($res[0]) )
							{
								foreach($res[0] as $k => $arr )
								{
									if ( $k != 'id' ) $field_prefs['field_uni'] = $k;
								}
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
							
						}
						else
						{
							$help_arr = $field_prefs['options'];
							$dummy = $help_arr[null];
							unset($help_arr[null]);
							$field_prefs['options'] = array();
							$field_prefs['options'][null] = $dummy;

							foreach ($help_arr as $k => $meal) {
								$field_prefs['options'][$k] = $meal;
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
						}

						
						$vals = $this->$field;		


						$select = $form->create_multiple_select( $field.'[]', $field_prefs['options'], t($field, 'admin'), $field_prefs['field_uni'] );
							
							if (isset($vals)) $select->attributes['value'] = $vals;
							else              $select->attributes['value'] = array();
									


					break;
					case 'selects':
						$field_prefs['options'][null] = '----';


						if ( $type = $this->is_class_or_type($this, $field) )
						{
							$res = $this->db->get($type)->result($type);

							foreach($res as $row)
							{
								$field_prefs['options'][$row->id] = (string) $row;
							}

							if ( isset($res[0]) )
							{
								foreach($res[0] as $k => $arr )
								{
									if ( $k != 'id' ) $field_prefs['field_uni'] = $k;
								}
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
							
						}
						else
						{
							$help_arr = $field_prefs['options'];
							$dummy = $help_arr[null];
							unset($help_arr[null]);
							$field_prefs['options'] = array();
							$field_prefs['options'][null] = $dummy;

							foreach ($help_arr as $k => $meal) {
								$field_prefs['options'][$k] = $meal;
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
						}

						$multiple_wrap = $form->create_element('div', 'multiwidget-selects multiwidget-wrap multiples-'.$field );
						
						$vals = $this->$field;		

						if (!isset($settings['count'])) $settings['count'] = 4;

						$number_of_multiples = ( !empty($vals) AND is_array($vals) AND count($vals) > $settings['count'] ) ? count($vals) + 1 : $settings['count'] + 1;	


						for($i = 0; $i < $number_of_multiples; $i++)
						{
							$field_name = $field.'['.$i.']';
							$field_readable = t($field).' '.($i+1);
							$div = $form->create_element('div', 'field-wrap multiple field-'.$field_name );
								$select = $form->create_select( $field_name, $field_prefs['options'], $field_readable, $field_prefs['field_uni'] );

									if (isset($vals[$i]->id)) $select->attributes['value'] = $vals[$i]->id;
									else                      $select->attributes['value'] = null;
								
							$form->end_element($div);
						}

						$form->end_element($multiple_wrap);

					break;
					case 'checkboxes':
						if ( $type = $this->is_class_or_type($this, $field) )
						{
							$res = $this->db->get($type)->result($type);

							foreach($res as $row)
							{
								$field_prefs['options'][$row->id] = (string) $row;
							}

							if ( isset($res[0]) )
							{
								foreach($res[0] as $k => $arr )
								{
									if ( $k != 'id' ) $field_prefs['field_uni'] = $k;
								}
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
						}
						else
						{
							$help_arr = $field_prefs['options'];
							$dummy = $help_arr[null];

							unset($help_arr[null]);

							$field_prefs['options'] = array();

							foreach ($help_arr as $k => $item) {
								$field_prefs['options'][$k] = $item;
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
						}

						if ( $this->is_valid() )
						{
							$this->get_with_relations(array('id' => $this->id));

						}
						else
						{
							$this->$field = array();
						}
						
						$vals = $this->$field;	


						$vals_arr = array();

						foreach($vals as $val)
						{
							$vals_arr[$val->id] = $val;
						}	

						foreach($field_prefs['options'] as $id => $val)
						{
							$div = $form->create_element('div', "multiple-item item-of-$id" );	

								$checkbox = $form->create_input('checkbox', $field.'[]', $val); 
								$checkbox->attributes['value'] = $id;

								if ( isset($vals_arr[$id]) )
								{
									$checkbox->attributes['checked'] =  $id;
								}

							$form->end_element($div);
						}

					break;
					case 'password':
						$field = $form->create_input( 'password', $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
					break;
					case 'textarea':
						$field = $form->create_textarea( $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
					break;
					case 'wysiwyg':
						$field = $form->create_textarea( $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
							$field->attributes['class'] = 'ckeditor ';
					break;
					case 'checkbox':
						$checkbox = $form->create_input('checkbox', $field, t($field, 'admin')); 
							$checkbox->attributes['value'] = $field_prefs['value'];
					break;
					case 'number':
						$field = $form->create_input( 'number', $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
							$field->attributes['step'] = 'any';
					break;
					case 'autodate':
						$field = $form->create_input( 'date', $field, t($field, 'admin') );
							$field->attributes['value'] = (isset( $field_prefs['value']) AND !empty($field_prefs['value']) ) ? date('Y-m-d', strtotime($field_prefs['value'])) : date('Y-m-d');
							
					break;
					case 'autoyear':
						$field = $form->create_input( 'date', $field, t($field, 'admin') );
							$field->attributes['value'] = (isset( $field_prefs['value']) AND !empty($field_prefs['value']) ) ? date('Y', strtotime($field_prefs['value'])) : date('Y');
							
					break;
				}

			$form->end_element( $div );
		}

		$form->addSubmit('login', 'Log in');
	
		return $form;	
	}

	public function create_form($action = '.', $action_params = array() )
	{
		//prer($this);
		$this->load->library('ci_form');
		$factory = clone $this->ci_form;

		$table_name = self::model_to_table_name( $this );

		$form = $factory->create_form( $action, $action_params );

		$form->form_id = $table_name.'-form';

		$class_name = self::model_to_table_name($this);

		$field_data = $this->get_field_data($class_name);

		if ( isset($this->field_settings) ) $fields_set = true;


		$class_name = self::model_to_table_name($this);

		foreach( $this as $field => $val )
		{
			if ( is_array($val) AND !$this->is_set_as_type($field) ) continue;

			$field_prefs = array('widget' => 'text', 'field_uni' => (string) $this, 'value' => $val, 'options' => array());
	
				
			if ( isset($field_data[$field]) )
			{
					switch ($field_data[$field]->type) {
					case 'text':
						$field_prefs['widget'] = 'text';
					break;
					
					case 'tinyint':
						$field_prefs['widget'] = 'checkbox';
					break;
					case 'int':
						$field_prefs['widget'] = 'number';
					break;
				}
			}

			//finding widgets
			
			if ( !$this->is_public($prop) ) continue;
			if ( class_exists($field) ) $field_prefs['widget'] = 'select';


			if ( $field_settings = $this->$class_name->get_field_settings() )
			{
				if ( isset($field_settings[$field]) )
				{
					$settings = $field_settings[$field];

					$field_prefs['value'] = (!empty($val)) ? (!empty($settings['value'])) ? $settings['value'] : $val : '';

					if ( is_object($field_prefs['value']) )
					{
						$field_prefs['value'] = $field_prefs['value']->id;
					}

					$field_prefs['options'] = (!empty($settings['options'])) ? $settings['options'] : $field_prefs['options'];

					if (isset( $settings['widget'])) $field_prefs['widget'] = $settings['widget'] ;
					if (isset($settings['count']))   $field_prefs['count'] = $settings['count'];
					else                             $field_prefs['count'] = 2;


					if ( isset($settings['type']) AND class_exists($settings['type']) AND (!isset($settings['widget']) OR !$this->is_multiple($field)) )
					{
						$field_prefs['widget'] = 'select';
						$table_name = $settings['type']; //equals class name
						$options = $this->db->get( $table_name )->result($table_name);


						foreach( $options as $option )
						{
							$field_prefs['options'][$option->id] = (string) $option;
						}
					}
				}
			}	

		
			$div = $form->create_element('div', 'field-wrap multiple-fields field-'.$field );	

			if ( $this->is_pk($field) AND !empty($val) ) 
			{
				$field = $form->create_input( 'hidden', $field, $val );
					$field->attributes['value'] = $val;

				$form->end_element( $div );
				continue;
			}


				switch($field_prefs['widget'])
				{
					case 'text':
						$field = $form->create_input( 'text', $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
					break;
					case 'file':
						$field = $form->create_input( 'file', $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
					break;
					case 'hidden':
						$field = $form->create_input( 'hidden', $field, '' );
							$field->attributes['value'] = $field_prefs['value'];
					break;
					case 'select':


						$field_prefs['options'][null] = '----';

						if ( class_exists($field) OR $this->is_set_as_type($field) )
						{
							if ( $this->is_set_as_type($field) )
							{
								$type = $this->get_type_of($field);
							} 
							else
							{
								$type = $field;
							}

							$res = $this->db->get($type)->result($type);

							foreach($res as $row)
							{
								$field_prefs['options'][$row->id] = (string) $row;
							}

							if ( isset($res[0]) )
							{
								foreach($res[0] as $k => $arr )
								{
									if ( $k != 'id' ) $field_prefs['field_uni'] = $k;
								}
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}

							
						}
						else
						{
							$type = $field;
							$help_arr = $field_prefs['options'];
							$dummy = $help_arr[null];

							unset($help_arr[null]);
							
							$field_prefs['options'] = array();
							$field_prefs['options'][null] = $dummy;

							foreach ($help_arr as $k => $meal) 
							{
								$field_prefs['options'][$k] = $meal;
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
						}

						$options = array(null => '------');
						$options = $field_prefs['options'] + $options; 

						$select = $form->create_select( $type, $field_prefs['options'], t($field, 'admin'), $field_prefs['field_uni'] );
							$select->attributes['value'] = $field_value;
					break;
					case 'multiple':
						$field_prefs['options'][null] = '----';


						if ( $type = $this->is_class_or_type($this, $field) )
						{
							$res = $this->db->get($type)->result($type);

							foreach($res as $row)
							{
								$field_prefs['options'][$row->id] = (string) $row;
							}

							if ( isset($res[0]) )
							{
								foreach($res[0] as $k => $arr )
								{
									if ( $k != 'id' ) $field_prefs['field_uni'] = $k;
								}
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
							
						}
						else
						{
							$help_arr = $field_prefs['options'];
							$dummy = $help_arr[null];
							unset($help_arr[null]);
							$field_prefs['options'] = array();
							$field_prefs['options'][null] = $dummy;

							foreach ($help_arr as $k => $meal) {
								$field_prefs['options'][$k] = $meal;
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
						}

						
						$vals = $this->$field;		


						$select = $form->create_multiple_select( $field.'[]', $field_prefs['options'], t($field, 'admin'), $field_prefs['field_uni'] );
							
							if (isset($vals)) $select->attributes['value'] = $vals;
							else              $select->attributes['value'] = array();
									


					break;
					case 'selects':
						$field_prefs['options'][null] = '----';


						if ( $type = $this->is_class_or_type($this, $field) )
						{
							$res = $this->db->get($type)->result($type);

							foreach($res as $row)
							{
								$field_prefs['options'][$row->id] = (string) $row;
							}

							if ( isset($res[0]) )
							{
								foreach($res[0] as $k => $arr )
								{
									if ( $k != 'id' ) $field_prefs['field_uni'] = $k;
								}
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
							
						}
						else
						{
							$help_arr = $field_prefs['options'];
							$dummy = $help_arr[null];
							unset($help_arr[null]);
							$field_prefs['options'] = array();
							$field_prefs['options'][null] = $dummy;

							foreach ($help_arr as $k => $meal) {
								$field_prefs['options'][$k] = $meal;
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
						}

						$multiple_wrap = $form->create_element('div', 'multiwidget-selects multiwidget-wrap multiples-'.$field );
						
						$vals = $this->$field;		

						if (!isset($settings['count'])) $settings['count'] = 4;

						$number_of_multiples = ( !empty($vals) AND is_array($vals) AND count($vals) > $settings['count'] ) ? count($vals) + 1 : $settings['count'] + 1;	


						for($i = 0; $i < $number_of_multiples; $i++)
						{
							$field_name = $field.'['.$i.']';
							$field_readable = t($field).' '.($i+1);
							$div = $form->create_element('div', 'field-wrap multiple field-'.$field_name );
								$select = $form->create_select( $field_name, $field_prefs['options'], $field_readable, $field_prefs['field_uni'] );

									if (isset($vals[$i]->id)) $select->attributes['value'] = $vals[$i]->id;
									else                      $select->attributes['value'] = null;
								
							$form->end_element($div);
						}

						$form->end_element($multiple_wrap);

					break;
					case 'checkboxes':
						if ( $type = $this->is_class_or_type($this, $field) )
						{
							$res = $this->db->get($type)->result($type);

							foreach($res as $row)
							{
								$field_prefs['options'][$row->id] = (string) $row;
							}

							if ( isset($res[0]) )
							{
								foreach($res[0] as $k => $arr )
								{
									if ( $k != 'id' ) $field_prefs['field_uni'] = $k;
								}
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
						}
						else
						{
							$help_arr = $field_prefs['options'];
							$dummy = $help_arr[null];

							unset($help_arr[null]);

							$field_prefs['options'] = array();

							foreach ($help_arr as $k => $item) {
								$field_prefs['options'][$k] = $item;
							}

							if ( is_object($val) )
							{
								$field_value = $val->id;
							}
							else
							{
								$field_value = $val;
							}
						}

						if ( $this->is_valid() )
						{
							$this->get_with_relations(array('id' => $this->id));

						}
						else
						{
							$this->$field = array();
						}
						
						$vals = $this->$field;	


						$vals_arr = array();

						foreach($vals as $val)
						{
							$vals_arr[$val->id] = $val;
						}	

						foreach($field_prefs['options'] as $id => $val)
						{
							$div = $form->create_element('div', "multiple-item item-of-$id" );	

								$checkbox = $form->create_input('checkbox', $field.'[]', $val); 
								$checkbox->attributes['value'] = $id;

								if ( isset($vals_arr[$id]) )
								{
									$checkbox->attributes['checked'] =  $id;
								}

							$form->end_element($div);
						}

					break;
					case 'password':
						$field = $form->create_input( 'password', $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
					break;
					case 'textarea':
						$field = $form->create_textarea( $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
					break;
					case 'wysiwyg':
						$field = $form->create_textarea( $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
							$field->attributes['class'] = 'ckeditor ';
					break;
					case 'checkbox':
						$checkbox = $form->create_input('checkbox', $field, t($field, 'admin')); 
							$checkbox->attributes['value'] = $field_prefs['value'];
					break;
					case 'number':
						$field = $form->create_input( 'number', $field, t($field, 'admin') );
							$field->attributes['value'] = $field_prefs['value'];
							$field->attributes['step'] = 'any';
					break;
					case 'autodate':
						$field = $form->create_input( 'date', $field, t($field, 'admin') );
							$field->attributes['value'] = (isset( $field_prefs['value']) AND !empty($field_prefs['value']) ) ? date('Y-m-d', strtotime($field_prefs['value'])) : date('Y-m-d');
							
					break;
					case 'autoyear':
						$field = $form->create_input( 'date', $field, t($field, 'admin') );
							$field->attributes['value'] = (isset( $field_prefs['value']) AND !empty($field_prefs['value']) ) ? date('Y', strtotime($field_prefs['value'])) : date('Y');
							
					break;
				}

			$form->end_element( $div );
		}

		$submit_area = $form->create_element('div', 'field-wrap submit-area' );

		$submit = $form->create_input('submit', '', 'Submit');
			$submit->attributes['value'] = t('save');
			$submit->classes[] = 'save-button btn btn-success';

		$form->end_element( $submit_area );
	
		return $form;
	}

	protected function is_pk( $field )
	{
		return ( $field == 'id' );
	}

	

	public function get_unicode_field()
	{
		$uni_field = '';
		foreach($this as $prop => $val)
		{
			if ( $prop != 'id' )
			{
				$uni_field = $prop;
				break;
			}
		} 

		return $uni_field;
	}

	public function retrieve_class($prop)
	{
		return $this->$prop;
	}


	public function order_by($col, $direction)
	{
		if (is_null($col))
		{
			$this->db_query = $this->db_query->order_by($this->get_unicode_field(), $direction); 
		}
		else
		{
			$this->db_query = $this->db_query->order_by($col, $direction);
		}

		return $this;
	}

	public function with_relations()
	{
		$table_name = self::model_to_table_name($this);
		$fin = array();
		
		$multis = $this->get_multiple_fields($this);

		foreach($this as $prop => $val)
		{
			if ($type = $this->get_type_of($prop))
			{
				$this_prop = &$this->$prop;
				$this_prop = $this->relation($type);
			}
		}

		if ( !empty($multis) AND count($this) == 1 )
		{
			foreach( $multis as $multiple_field )
			{
				$prop_arr = array();
				$target_table = $this->get_type_of( $multiple_field );
				$connection_table = $this->create_connection_table_name( $table_name, $target_table );
				$q = $this->db->select("$target_table.*")
								  ->where($table_name, $this->id)
								  ->join($target_table, "$connection_table.$target_table = $target_table.id", 'LEFT')
								  ->order_by("$connection_table.id", 'asc')
								  //->order_by("$connection_table.$order_field", $order_direction)
								  ->get($connection_table)
								  ->result($target_table);

				$this->$multiple_field = $q;
			}
			
		}

		return $this;
	}

	public function row()
	{
		$this_table = self::model_to_table_name($this);
		$row = $this->db_query->get($this_table)->row();

		$this->remove_query();

		if (empty($row)) return false;

		$class = get_class($this);
		$new_inst = new $class(false);

		foreach($row as $prop => $val)
		{
			if ( !$this->is_multiple($prop) )
			{
				$new_inst->$prop = $val;
			}
			
		}

		$new_inst->remove_query();

		return $new_inst;
	}

	public function result()
	{
		$this_table = self::model_to_table_name($this);
		$this_type = strtolower(get_class($this));

		$instances = array();
		foreach( $this->db_query->get($this_table)->result($this_table) as $row)
		{
			$instance = new $this_type(false);
			foreach($row as $prop => $val)
			{
				$instance->$prop = $val;
				$instance->remove_query();
			}



			$instances[] = $instance;
		}

		unset($this->db_query);

		return $instances;
	}

	public function m_to_n_join($multiple_field, $return_query)
	{
		$prop_arr = array();
		$table_name = self::model_to_table_name( $this );
		$field_settings = $this->get_field_settings( $multiple_field );
		$target_table = $this->get_type_of( $multiple_field );
		$connection_table = $this->create_connection_table_name( $table_name, $target_table );

		$tables = $this->db->list_tables();

		if ( !in_array($connection_table, $tables) ) $connection_table = $this->create_connection_table_name( $target_table, $table_name );


		$conn_table_fields = $this->db->list_fields( $connection_table );

		$selected_string = "$target_table.*, ";
		foreach( $conn_table_fields as $field )
		{
			$selected_string .= " $connection_table.$field as conn_$field, ";
		}

	    $conns = $this->db->select($selected_string)	
							->where("$connection_table.$table_name", $this->id)
							->join($target_table, "$connection_table.$target_table = $target_table.id", 'LEFT')
							->order_by("$connection_table.id", 'asc');
		if ($return_query)
		{
			return $conns;
		}					

		$conns = $conns->get($connection_table)->result($target_table);

		$multiple_field = &$this->$multiple_field;

		$multiple_field = $conns;				

		return $conns;
	}

	public function get_multiple_fields( $obj )
	{
		$multis = array();
		foreach( $obj as $prop => $val )
		{
			if ( $obj->is_multiple($prop) ) $multis[] = $prop;
		}
		return $multis
		;
	}
	

	public function delete($id = null)
	{
		$table_name = self::model_to_table_name($this);

		if ( is_null($id) ) $id = $this->id; 


		$multiple_filed = array();
		foreach( $this as $prop => $val )
		{
			if ( $this->is_multiple($prop) ) $multiple_field[] = $prop;
		}


		if ( !empty($multiple_field) )
		{
			foreach( $multiple_field as $prop )
			{
				$settings = $this->get_field_settings( $prop );
				$type = $settings['type'];
				$connection_table_name = $this->create_connection_table_name( $table_name, $type );

				$this->db->where($table_name, $id)->delete( $connection_table_name );

			}
		}

		return $this->db->where('id', $id)->delete($table_name);

		
	}

	public function get_field_data( $table_name )
	{

		if ( $this->db->table_exists( $table_name ) )
		{
			$fin = array();
			foreach( $this->db->field_data( $table_name ) as $field )
			{	
				$fin[$field->name] = $field;
			}

			return $fin;
		}
		
	}


	public function get_field_settings( $key = null, $val_of_key = null)
	{
		if ( is_null($key) )
		{
			return (isset($this->field_settings)) ? $this->field_settings : false;
		}
		elseif(is_null($val_of_key))
		{
			if ( isset($this->field_settings) )
			{
				if ( isset($this->field_settings[$key]) )
				{
					return $this->field_settings[$key];
				} 
			}

			return false;
		}
		else
		{
			if ( isset($this->field_settings) )
			{
				if ( isset($this->field_settings[$key]) )
				{
					if ( isset($this->field_settings[$key][$val_of_key]) ) return $this->field_settings[$key][$val_of_key];	
				}	 
			}
			return false;
		}		
	}

	public function prop_as_model( $prop )
	{
		$class_name = self::model_to_table_name($this);
		$settings = $this->$class_name->get_field_settings();

		if ( isset($settings[$prop]) )
		{
			if ( isset($settings[$prop]['type']) )
			{
				if ( class_exists($settings[$prop]['type']) )
				{
					return $settings[$prop]['type'];
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public function is_select_with_options($instance, $prop)
	{
		if ( $settings = $instance->get_field_settings($prop) )
		{
			if ( isset($settings['widget']) AND $settings['widget'] == 'select' AND isset($settings['options']) )
			{
				return $settings['options'];
			}
			else
			{
				return false;
			}
		}	
		else
		{
			return false;
		}
	}

	public function is_class_or_type( $instance, $prop )
	{
		if ( !is_object($instance) ) return false;
		if ( class_exists($prop) ) return $prop; 

		$settings = $instance->get_field_settings();

		$type = (isset($settings[$prop]['type'])) ? $settings[$prop]['type'] : false ;
		return (class_exists( $type )) ? $type : false;	
	} 

	public function is_set_as_type( $field )
	{
		if ( $this->get_field_settings() )
		{
			$settings = $this->get_field_settings();

			return isset($settings[$field]['type']); 
		}
	}

	public function is_multiple($prop)
	{
		if ( $field_settings = $this->get_field_settings($prop) ) 
		{

			return ( (isset($field_settings['widget']) AND $field_settings['widget'] == 'multiple') OR isset($field_settings['multiple']) );
		}
		else
		{
			return false;
		}
	}

	public function is_select( $prop )
	{
		if ( $settings = $this->get_field_settings($prop) )
		{
			if ( (isset($settings['widget']) AND $settings['widget'] == 'select' || isset($settings['options']) ) )
			{
				return $settings['options'];
			}
		}

		return false;
	}

	public function get_type_of( $field )
	{
		if ( $this->is_set_as_type( $field ) )
		{
			$settings = $this->get_field_settings();

			return $settings[$field]['type'];
		}
		return $field;

	}

	public function get_field_of_type( $model_name, $type )
	{
		$instance = $this->basemodel->retrieve_class( $model_name );
		$type = (!is_string($type)) ? self::model_to_table_name($type) : $type;

		foreach($instance as $prop => $val)
		{
			if ( $prop == $type ) return $type;
		}

		if ( $settings = $instance->get_field_settings() )
		{	
			foreach( $settings as $field => $prefs )
			{
				if ( isset($prefs['type']) AND $prefs['type'] == $type ) return $field;
			}

		}

		return false;
	}

	public function get_instances_for_select()
	{
		$this_table = self::model_to_table_name($this);
		$fin = array();
		$insts = $this->get()->result();
		foreach ($insts as $inst) 
		{
			$fin[$inst->id] = (string) $inst;
		}

		return $fin;
	}

	public function is_conected_m_to_n( $field )
	{
		$settings = $this->get_field_settings();

		if ( !$settings ) return false;

		foreach( $settings as $field_name => $field_settings )
		{
			if ( $this->is_multiple( $field_name ) AND $field_name == $field ) return $field_name;
		}	

		return false;
	}

	public function relation( $model_name, $return_query = false )
	{

		if (!isset($this->id) OR !$this->is_model($model_name))
		{
			return false; //this instance is not valid - does not contains id
		}	

		$this_model_name = $this->model_to_table_name($this); //tables have same name as their models

		//First case - this model has property that is reference to some other table
		$referencing_field = $this->get_field_of_type($this_model_name, $model_name); //second parameter is type

		if ($referencing_field AND !$this->is_multiple($referencing_field))
		{
			return $this->_local_relation($referencing_field, $model_name, $return_query);
		}

		if ($this->is_multiple($referencing_field))
		{
			return $this->m_to_n_join($referencing_field, $return_query);
		}


		//Second model of name $model_name may have reference to this model
		
		$referencing_field = $this->get_field_of_type($model_name, $this_model_name, $return_query); //looking for field in other table whose type is this model


		if ($referencing_field)
		{
			return $this->_remote_relation($referencing_field, $model_name, $return_query);
		}
		else
		{
			return array();
		}
	}

	protected function _local_relation($referencing_field, $requested_model, $return_query)
	{
		$this_model_name = $this->model_to_table_name($this); //tables have same name as their models

		$referencing_id = $this->$referencing_field;

		if (empty($referencing_id) OR !is_numeric($referencing_id)) 
		{
			return false; //no id to use for query	
		}

		if ($return_query)
		{
			return $this->db->where('id', $referencing_id);
		}


		return $requested_model::get($referencing_id)->row();
	}

	protected function _remote_relation($referencing_field, $remote_model_name, $return_query)
	{
		if ($return_query)
		{
			return $this->db->where($referencing_field, $this->id);
		}


		return $remote_model_name::get(array($referencing_field => $this->id))->result();
	}

	public function remove_query()
	{
		echo 'asf';
		echo '<br>';
		echo get_class($this);
		if (!isset($this->db_query)) return false;
		unset($this->db_query);
	}


	public function is_related_to($model_name)
	{
		$tables = $this->db->list_tables();
		$this_table = self::model_to_table_name($this);


		$as_source = $this->create_connection_table_name( $this_table, $model_name );


		if (in_array($as_source, $tables)) return $model_name;

		$as_tar = $this->create_connection_table_name( $model_name, $this_table );


		if (in_array($as_tar, $tables)) return $model_name;

		return false;
	}

	public function is_valid()
	{
		return isset($this->id);
	}

	public function translatable()
	{
		$translation = $this->basemodel->retrieve_class('translation');

		$translation->register($this);
		$translation->create_translation_table();
	}

	public function get_translated($where)
	{	
		$this_table_name = self::model_to_table_name($this);

		$instance = $this->get($where);
		$instance = (isset($instance[0])) ? $instance[0] : $instance;

		return $instance->translate();
	}

	public function translate( $lang = '' )
	{
		$this_table_name = self::model_to_table_name( $this );

		$translations = $this->translation->get(array('model' => $this_table_name, 'model_id' => $this->id));
		
		if ( !$translations[0]->is_valid() ) return false;
		else $translations = $translations[0];

		foreach ($translations as $prop => $trans) 
		{
			if ( is_array($trans) ) continue;

			$trans_string = str_replace('_'.$lang, '', $prop);

			if ( $this->is_translatable($trans_string) )
			{
				$this->$trans_string = $trans;			
			}
			
		}

		return $this;
	}

	public function is_translatable($prop)
	{
		$field_settings = $this->get_field_settings($prop);

		return isset($field_settings['translate']);
	}

	public function upload($files_field, $path, $desired_filename = null)
	{
		$source_file = $_FILES[$files_field];

		if ( !is_dir($path) ) 
		{
			mkdir($path, 0777);
			@chmod($path. 0777);
		}

		$config['upload_path'] = $path;
		$config['file_name'] = (is_null($desired_filename)) ? $source_file['name'] : $desired_filename;

		if ( !$allowed_files = $this->config->item('upload_allowed_file_types') )
		{
			$allowed_files = array('png', 'jpg', 'jpeg', 'gif');
		}

		$config['allowed_types'] = implode('|', $allowed_files);

		$this->load->library('upload', $config);

		if ( $this->upload->do_upload($files_field) )
		{
			$upload_data = $this->upload->data();

			return $upload_data['file_name'];
		}

		return false;
	}

	public function get_referencing_field_to($model_name)
	{
		foreach($this as $prop => $value)
		{
			if ($prop == $model_name) return $prop;
			
			if (!$sets = $this->get_field_settings($prop)) continue;

			if (isset($sets['type']) AND $sets['type'] == $model_name) return $prop;

		}

		return false;
	}

	

	public function get_query()
	{
		return $this->db_query;
	}

	public static function get($where = array())
	{
		$inst = new static;
		$table_name = self::model_to_table_name($inst);
		$fin = array();
		$ci =& get_instance();

		$res = $ci->db;

		if ( !empty($where) AND !is_numeric($where) ) 
		{
			$fin_where = array();
			foreach($where as $k => $val)
			{
				$fin_where[ $table_name.'.'.$k ] = $val;
			}

			$res = $res->where($fin_where);
		}  
		elseif (is_numeric($where)) //where is integer, perhaps it is ID itself
		{
			$res = $res->where('id', $where);
		}
		$inst->db_query = $res;

		$ci->load->library('ci_collection', array($inst, $inst->db_query));
		return $ci->ci_collection;
	}

	public static function db()
	{
		$ci = &get_instance();

		return $ci->db;
	}

	

}

class BasemodelException extends Exception {}

/* End of file basemodel.php */
/* Location: ./application/models/basemodel.php */