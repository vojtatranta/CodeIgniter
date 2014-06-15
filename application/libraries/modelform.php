<?php


require 'forms/autoload.php';

use Nette\Forms\Form;

class ModelForm extends Form {

	public $form;

	private $instance;

	public function __construct($instance)
	{
		$this->instance = $instance[0];

		$this->form = new Form;
	}

	public function __toString()
	{
		return $this->_assemble_form(true);
	}

	public function _assemble_form($as_string = false)
	{
		foreach ($this->instance as $prop => $val) 
		{
			$settings = $this->instance->get_field_settings($prop);

			if ( !$settings OR !isset($settings['widget']) ) 
			{
				$this->form->addText($prop, $prop);	
				continue;
			}

			$widget = $settings['widget'];

			$potential_method = 'add'.ucfirst($widget);
			if ( method_exists($this->form, $potential_method) )
			{
				call_user_func_array(array($this->form, $potential_method), array($prop, $prop));
			}

		}

		prer($this->instance->get_table_structure());

		$as_arr = (array) $this->instance;
		$this->form->setDefaults( $as_arr );

		return $as_string ? (string) $this->form : $this->form;
	}

	public function get_elem($which)
	{
		return $this->form[$which];
	}

/*
	public function exclude($what)
	{
		if (is_array($what))
		{
			foreach($what as $elem_name)
			{
				try {
					unset($this->form[$elem_name]);
				} catch (Exception $e) {
					continue;
				}
			}
		}
		else
		{
			try {
				unset($this->form[$what]);
			} catch (Exception $e) {
				return false;
			}
		}
	}
*/

}