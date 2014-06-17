<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Student extends Basemodel {
	public $firstname = '';
	public $familyname = '';
	public $telephone = '';
	public $email = '';
	public $created = '';

	protected static $field_settings = array(
		'firstname'  => array('rules'  => array('required')),
		'familyname' => array('rules'  => array('required')),
		'email' 	 => array('rules'  => array('required')),
		'created' 	 => array('widget' => 'hidden')
	);
	
	public function __construct()
	{
		parent::__construct();
		
		$this->sync_db();
	}

	public function get_unicode()
	{
		return $this->fullname();
	}

	public function fullname()
	{
		return $this->firstname .' '. $this->familyname;
	}

	public function hook_save()
	{
		if (!isset($this->id)) $this->created = date('Y-m-d H:i:s');
	}
}

/* End of file student.php */
/* Location: ./application/models/student.php */